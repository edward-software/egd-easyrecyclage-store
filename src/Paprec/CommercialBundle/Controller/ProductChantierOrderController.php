<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierOrderLine;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderLineAddType;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderLineEditType;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductChantierOrderController extends Controller
{

    /**
     * @Route("/productChantierOrder", name="paprec_commercial_productChantierOrder_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductChantierOrder:index.html.twig');
    }

    /**
     * @Route("/productChantierOrder/loadList", name="paprec_commercial_productChantierOrder_loadList")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function loadListAction(Request $request)
    {
        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $orders = $request->get('order');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'p.businessName', 'method' => array('getBusinessName'));
        $cols['totalAmount'] = array('label' => 'totalAmount', 'id' => 'p.totalAmount', 'method' => array('getTotalAmount'));
        $cols['orderStatus'] = array('label' => 'orderStatus', 'id' => 'p.orderStatus', 'method' => array('getOrderStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'p.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductChantierOrder', 'p')
            ->where('p.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.businessName', '?1'),
                    $queryBuilder->expr()->like('p.totalAmount', '?1'),
                    $queryBuilder->expr()->like('p.orderStatus', '?1'),
                    $queryBuilder->expr()->like('p.dateCreation', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $orders, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/productChantierOrder/export", name="paprec_commercial_productChantierOrder_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductChantierOrder', 'p')
            ->where('p.deleted IS NULL');

        $productChantierOrders = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Commandes Chantier")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Secteur d\'activité')
            ->setCellValue('D1', 'Civilité')
            ->setCellValue('E1', 'Nom')
            ->setCellValue('F1', 'Prénom')
            ->setCellValue('G1', 'Email')
            ->setCellValue('H1', 'Adresse')
            ->setCellValue('I1', 'Code postal')
            ->setCellValue('J1', 'Ville')
            ->setCellValue('K1', 'Téléphone')
            ->setCellValue('L1', 'Statut')
            ->setCellValue('M1', 'Montant total')
            ->setCellValue('N1', 'Méthode de paiement')
            ->setCellValue('O1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Commandes Chantier');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productChantierOrders as $productChantierOrder) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productChantierOrder->getId())
                ->setCellValue('B' . $i, $productChantierOrder->getBusinessName())
                ->setCellValue('C' . $i, $productChantierOrder->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productChantierOrder->getCivility())
                ->setCellValue('E' . $i, $productChantierOrder->getLastName())
                ->setCellValue('F' . $i, $productChantierOrder->getFirstName())
                ->setCellValue('G' . $i, $productChantierOrder->getEmail())
                ->setCellValue('H' . $i, $productChantierOrder->getAddress())
                ->setCellValue('I' . $i, $productChantierOrder->getPostalCode())
                ->setCellValue('J' . $i, $productChantierOrder->getCity())
                ->setCellValue('K' . $i, $productChantierOrder->getPhone())
                ->setCellValue('L' . $i, $productChantierOrder->getOrderStatus())
                ->setCellValue('M' . $i, $productChantierOrder->getTotalAmount())
                ->setCellValue('N' . $i, $productChantierOrder->getPaymentMethod())
                ->setCellValue('O' . $i, $productChantierOrder->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Commandes-Chantier-' . date('Y-m-d') . '.xlsx';

        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/productChantierOrder/view/{id}", name="paprec_commercial_productChantierOrder_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, ProductChantierOrder $productChantierOrder)
    {
        return $this->render('PaprecCommercialBundle:ProductChantierOrder:view.html.twig', array(
            'productChantierOrder' => $productChantierOrder
        ));
    }

    /**
     * @Route("/productChantierOrder/edit/{id}", name="paprec_commercial_productChantierOrder_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, ProductChantierOrder $productChantierOrder)
    {

        $status = array();
        foreach ($this->getParameter('paprec_order_status') as $s) {
            $status[$s] = $s;
        }

        $paymentMethods = array();
        foreach ($this->getParameter('paprec_order_payment_methods') as $p) {
            $paymentMethods[$p] = $p;
        }

        $form = $this->createForm(ProductChantierOrderType::class, $productChantierOrder, array(
            'status' => $status,
            'paymentMethods' => $paymentMethods
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierOrder = $form->getData();
            $productChantierOrder->setDateUpdate(new \DateTime());

            if ($productChantierOrder->getAssociatedInvoice() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $associatedInvoice = $productChantierOrder->getAssociatedInvoice();
                $associatedInvoiceFileName = md5(uniqid()) . '.' . $associatedInvoice->guessExtension();

                $associatedInvoice->move($this->getParameter('paprec_commercial.product_chantier_order.files_path'), $associatedInvoiceFileName);

                $productChantierOrder->setAssociatedInvoice($associatedInvoiceFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productChantierOrder_view', array(
                'id' => $productChantierOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductChantierOrder:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantierOrder' => $productChantierOrder
        ));
    }

    /**
     * @Route("/productChantierOrder/remove/{id}", name="paprec_commercial_productChantierOrder_remove")
     * @Security("has_role('ROLE_ADMIN')")
     * @throws Exception
     */
    public function removeAction(Request $request, ProductChantierOrder $productChantierOrder)
    {
        $em = $this->getDoctrine()->getManager();

        if (!empty($productChantierOrder->getAssociatedInvoice())) {
            $this->removeFile($this->getParameter('paprec_commercial.product_chantier_order.files_path') . '/' . $productChantierOrder->getAssociatedInvoice());
            $productChantierOrder->setAssociatedInvoice();
        }

        $productChantierOrder->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productChantierOrder_index');
    }

    /**
     * @Route("/productChantierOrder/removeMany/{ids}", name="paprec_commercial_productChantierOrder_removeMany")
     * @Security("has_role('ROLE_ADMIN')")
     * @throws Exception
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if (is_array($ids) && count($ids)) {
            $productChantierOrders = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->findById($ids);
            foreach ($productChantierOrders as $productChantierOrder) {
                if (!empty($productChantierOrder->getAssociatedInvoice())) {
                    $this->removeFile($this->getParameter('paprec_commercial.product_chantier_order.files_path') . '/' . $productChantierOrder->getAssociatedInvoice());
                    $productChantierOrder->setAssociatedInvoice();
                }
                $productChantierOrder->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productChantierOrder_index');
    }

    /**
     * Suppression physique d'un fichier
     *
     * @param $path
     * @throws Exception
     */
    public function removeFile($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @Route("/productChantierOrder/{id}/addLine", name="paprec_commercial_productChantierOrder_addLine")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addLineAction(Request $request, ProductChantierOrder $productChantierOrder)
    {

        $em = $this->getDoctrine()->getManager();

        // Ces deux variables permettent de recharger le formulaire quand on choisit un produit
        // Au rechargement, on récupère uniquement les catégories liées au produit choisi
        $selectedProductId = $request->get('selectedProductId');
        $submitForm = $request->get('submitForm');

        if ($productChantierOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productChantierOrderLine = new ProductChantierOrderLine();

        $form = $this->createForm(ProductChantierOrderLineAddType::class, $productChantierOrderLine,
            array(
                'selectedProductId' => $selectedProductId
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $submitForm) {
            $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');

            $productChantierOrderLine = $form->getData();
            $productChantierOrderManager->addLine($productChantierOrder, $productChantierOrderLine);

            return $this->redirectToRoute('paprec_commercial_productChantierOrder_view', array(
                'id' => $productChantierOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductChantierOrderLine:add.html.twig', array(
            'form' => $form->createView(),
            'productChantierOrder' => $productChantierOrder,
        ));
    }

    /**
     * @Route("/productChantierOrder/{id}/editLine/{orderLineId}", name="paprec_commercial_productChantierOrder_editLine")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productChantierOrder", options={"id" = "id"})
     * @ParamConverter("productChantierOrderLine", options={"id" = "orderLineId"})
     */
    public function editLineAction(Request $request, ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        if ($productChantierOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierOrderLine->getProductChantierOrder() !== $productChantierOrder) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductChantierOrderLineEditType::class, $productChantierOrderLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');

            $productChantierOrderManager->editLine($productChantierOrder, $productChantierOrderLine);

            return $this->redirectToRoute('paprec_commercial_productChantierOrder_view', array(
                'id' => $productChantierOrder->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductChantierOrderLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantierOrder' => $productChantierOrder,
            'productChantierOrderLine' => $productChantierOrderLine
        ));
    }

    /**
     * @Route("/productChantierOrder/{id}/removeLine/{orderLineId}", name="paprec_commercial_productChantierOrder_removeLine")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productChantierOrder", options={"id" = "id"})
     * @ParamConverter("productChantierOrderLine", options={"id" = "orderLineId"})
     */
    public function removeLineAction(Request $request, ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        if ($productChantierOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierOrderLine->getProductChantierOrder() !== $productChantierOrder) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productChantierOrderLine);
        $em->flush();

        $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');
        $total = $productChantierOrderManager->calculateTotal($productChantierOrder);
        $productChantierOrder->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productChantierOrder_view', array(
            'id' => $productChantierOrder->getId()
        ));
    }

    /**
     * @Route("/productChantierOrder/{id}/downloadAssociatedInvoice", name="paprec_commercial_quoteRequest_downloadAssociatedInvoice")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function downloadAssociatedQuoteAction( ProductChantierOrder $productChantierOrder)
    {
        $filename = $productChantierOrder->getAssociatedInvoice();
        $path = $this->getParameter('paprec_commercial.product_chantier_order.files_path');
        $content = file_get_contents($path . '/' . $filename);
        $extension = pathinfo($path . '/' . $filename, PATHINFO_EXTENSION);

        $response = new Response();
        $newFilename = "Commande-Chantier-" . $productChantierOrder->getId() . '-Facture.' . $extension;

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $newFilename);

        $response->setContent($content);
        return $response;
    }
}
