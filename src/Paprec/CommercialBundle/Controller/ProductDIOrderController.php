<?php

namespace Paprec\CommercialBundle\Controller;

use Paprec\CommercialBundle\Entity\ProductDIOrder;
use Paprec\CommercialBundle\Entity\ProductDIOrderLine;
use Paprec\CommercialBundle\Form\ProductDICategoryAddType;
use Paprec\CommercialBundle\Form\ProductDICategoryEditType;
use Paprec\CommercialBundle\Form\ProductDIOrderLineAddType;
use Paprec\CommercialBundle\Form\ProductDIOrderLineEditType;
use Paprec\CommercialBundle\Form\ProductDIOrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Test\Fixture\Entity\Shop\Product;

class ProductDIOrderController extends Controller
{

    /**
     * @Route("/productDIOrder", name="paprec_commercial_productDIOrder_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:ProductDIOrder:index.html.twig');
    }

    /**
     * @Route("/productDIOrder/loadList", name="paprec_commercial_productDIOrder_loadList")
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
            ->from('PaprecCommercialBundle:ProductDIOrder', 'p')
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
     * @Route("/productDIOrder/export", name="paprec_commercial_productDIOrder_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCommercialBundle:ProductDIOrder', 'p')
            ->where('p.deleted IS NULL');

        $productDIOrders = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Devis DI")
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
            ->setCellValue('N1', 'CA généré')
            ->setCellValue('O1', 'Agence associée')
            ->setCellValue('P1', 'Résumé du besoin')
            ->setCellValue('Q1', 'Fréquence')
            ->setCellValue('R1', 'Tonnage')
            ->setCellValue('S1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Secteurs d\'activités');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productDIOrders as $productDIOrder) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productDIOrder->getId())
                ->setCellValue('B' . $i, $productDIOrder->getBusinessName())
                ->setCellValue('C' . $i, $productDIOrder->getBusinessLine()->getName())
                ->setCellValue('D' . $i, $productDIOrder->getCivility())
                ->setCellValue('E' . $i, $productDIOrder->getLastName())
                ->setCellValue('F' . $i, $productDIOrder->getFirstName())
                ->setCellValue('G' . $i, $productDIOrder->getEmail())
                ->setCellValue('H' . $i, $productDIOrder->getAddress())
                ->setCellValue('I' . $i, $productDIOrder->getPostalCode())
                ->setCellValue('J' . $i, $productDIOrder->getCity())
                ->setCellValue('K' . $i, $productDIOrder->getPhone())
                ->setCellValue('L' . $i, $productDIOrder->getOrderStatus())
                ->setCellValue('M' . $i, $productDIOrder->getTotalAmount())
                ->setCellValue('N' . $i, $productDIOrder->getGeneratedTurnover())
                ->setCellValue('O' . $i, $productDIOrder->getAgency())
                ->setCellValue('P' . $i, $productDIOrder->getSummary())
                ->setCellValue('Q' . $i, $productDIOrder->getFrequency())
                ->setCellValue('R' . $i, $productDIOrder->getTonnage())
                ->setCellValue('S' . $i, $productDIOrder->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Devis-DI-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productDIOrder/view/{id}", name="paprec_commercial_productDIOrder_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, ProductDIOrder $productDIOrder)
    {
        return $this->render('PaprecCommercialBundle:ProductDIOrder:view.html.twig', array(
            'productDIOrder' => $productDIOrder
        ));
    }

    /**
     * @Route("/productDIOrder/add", name="paprec_commercial_productDIOrder_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {

        $productDIOrder = new ProductDIOrder();

        $status = array();
        foreach ($this->getParameter('paprec_order_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ProductDIOrderType::class, $productDIOrder, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDIOrder = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($productDIOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productDIOrder_view', array(
                'id' => $productDIOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIOrder:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productDIOrder/edit/{id}", name="paprec_commercial_productDIOrder_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, ProductDIOrder $productDIOrder)
    {

        $status = array();
        foreach ($this->getParameter('paprec_order_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(ProductDIOrderType::class, $productDIOrder, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDIOrder = $form->getData();
            $productDIOrder->setDateUpdate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_productDIOrder_view', array(
                'id' => $productDIOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIOrder:edit.html.twig', array(
            'form' => $form->createView(),
            'productDIOrder' => $productDIOrder
        ));
    }

    /**
     * @Route("/productDIOrder/remove/{id}", name="paprec_commercial_productDIOrder_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, ProductDIOrder $productDIOrder)
    {
        $em = $this->getDoctrine()->getManager();

        $productDIOrder->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_productDIOrder_index');
    }

    /**
     * @Route("/productDIOrder/removeMany/{ids}", name="paprec_commercial_productDIOrder_removeMany")
     * @Security("has_role('ROLE_ADMIN')")
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
            $productDIOrders = $em->getRepository('PaprecCommercialBundle:ProductDIOrder')->findById($ids);
            foreach ($productDIOrders as $productDIOrder) {
                $productDIOrder->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_productDIOrder_index');
    }

    /**
     * @Route("/productDIOrder/{id}/addLine", name="paprec_commercial_productDIOrder_addLine")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addLineAction(Request $request, ProductDIOrder $productDIOrder)
    {

        $em = $this->getDoctrine()->getManager();
        $selectedProductId = $request->get('selectedProductId');
        $submitForm = $request->get('submitForm');

        if ($productDIOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productDIOrderLine = new ProductDIOrderLine();

        $form = $this->createForm(ProductDIOrderLineAddType::class, $productDIOrderLine,
            array(
                'selectedProductId' => $selectedProductId
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $submitForm) {
            $productDIOrderManager = $this->get('paprec_catalog.product_di_order_manager');

            $productDIOrderLine = $form->getData();
            $productDIOrderManager->addLine($productDIOrder, $productDIOrderLine);

            return $this->redirectToRoute('paprec_commercial_productDIOrder_view', array(
                'id' => $productDIOrder->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:ProductDIOrderLine:add.html.twig', array(
            'form' => $form->createView(),
            'productDIOrder' => $productDIOrder,
        ));
    }

    /**
     * @Route("/productDIOrder/{id}/editLine/{orderLineId}", name="paprec_commercial_productDIOrder_editLine")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productDIOrder", options={"id" = "id"})
     * @ParamConverter("productDIOrderLine", options={"id" = "orderLineId"})
     */
    public function editLineAction(Request $request, ProductDIOrder $productDIOrder, ProductDIOrderLine $productDIOrderLine)
    {
        if ($productDIOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productDIOrderLine->getProductDIOrder() !== $productDIOrder) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductDIOrderLineEditType::class, $productDIOrderLine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productDIOrderManager = $this->get('paprec_catalog.product_di_order_manager');

            $productDIOrderManager->editLine($productDIOrder, $productDIOrderLine);

            return $this->redirectToRoute('paprec_commercial_productDIOrder_view', array(
                'id' => $productDIOrder->getId()
            ));
        }

        return $this->render('PaprecCommercialBundle:ProductDIOrderLine:edit.html.twig', array(
            'form' => $form->createView(),
            'productDIOrder' => $productDIOrder,
            'productDIOrderLine' => $productDIOrderLine
        ));
    }

    /**
     * @Route("/productDIOrder/{id}/removeLine/{orderLineId}", name="paprec_commercial_productDIOrder_removeLine")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productDIOrder", options={"id" = "id"})
     * @ParamConverter("productDIOrderLine", options={"id" = "orderLineId"})
     */
    public function removeLineAction(Request $request, ProductDIOrder $productDIOrder, ProductDIOrderLine $productDIOrderLine)
    {
        if ($productDIOrder->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productDIOrderLine->getProductDIOrder() !== $productDIOrder) {
            throw new NotFoundHttpException();
        }


        $em = $this->getDoctrine()->getManager();

        $em->remove($productDIOrderLine);
        $em->flush();

        $productDIOrderManager = $this->get('paprec_catalog.product_di_order_manager');
        $total = $productDIOrderManager->calculateTotal($productDIOrder);
        $productDIOrder->setTotalAmount($total);
        $em->flush();


        return $this->redirectToRoute('paprec_commercial_productDIOrder_view', array(
            'id' => $productDIOrder->getId()
        ));
    }
}
