<?php

namespace Paprec\CommercialBundle\Controller;

use Exception;
use Paprec\CommercialBundle\Entity\OrderRequest;
use Paprec\CommercialBundle\Form\OrderRequestEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

class OrderRequestController extends Controller
{
    /**
     * @Route("/orderRequest", name="paprec_commercial_orderRequest_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCommercialBundle:OrderRequest:index.html.twig');
    }

    /**
     * @Route("/orderRequest/loadList", name="paprec_commercial_orderRequest_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'o.id', 'method' => array('getId'));
        $cols['businessName'] = array('label' => 'businessName', 'id' => 'o.businessName', 'method' => array('getBusinessName'));
        $cols['division'] = array('label' => 'division', 'id' => 'o.division', 'method' => array('getDivision'));
        $cols['orderStatus'] = array('label' => 'orderStatus', 'id' => 'o.orderStatus', 'method' => array('getOrderStatus'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'o.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));


        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:OrderRequest', 'o')
            ->where('o.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('o.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('o.businessName', '?1'),
                    $queryBuilder->expr()->like('o.division', '?1'),
                    $queryBuilder->expr()->like('o.orderStatus', '?1'),
                    $queryBuilder->expr()->like('o.dateCreation', '?1')
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
     * @Route("/orderRequest/export", name="paprec_commercial_orderRequest_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('o'))
            ->from('PaprecCommercialBundle:OrderRequest', 'o')
            ->where('o.deleted IS NULL');

        $orderRequests = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Demandes de devis")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Raison sociale')
            ->setCellValue('C1', 'Civilité')
            ->setCellValue('D1', 'Nom')
            ->setCellValue('E1', 'Prénom')
            ->setCellValue('F1', 'Email')
            ->setCellValue('G1', 'Téléphone')
            ->setCellValue('H1', 'Statut')
            ->setCellValue('I1', 'Mon besoin')
            ->setCellValue('J1', 'CA généré')
            ->setCellValue('K1', 'Division')
            ->setCellValue('L1', 'Code postal')
            ->setCellValue('M1', 'Agence associée')
            ->setCellValue('N1', 'Résumé du besoin')
            ->setCellValue('O1', 'Fréquence')
            ->setCellValue('P1', 'Tonnage')
            ->setCellValue('Q1', 'N° Kookabura')
            ->setCellValue('R1', 'Date création');

        $phpExcelObject->getActiveSheet()->setTitle('Demandes de devis');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($orderRequests as $orderRequest) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $orderRequest->getId())
                ->setCellValue('B' . $i, $orderRequest->getBusinessName())
                ->setCellValue('C' . $i, $orderRequest->getCivility())
                ->setCellValue('D' . $i, $orderRequest->getLastName())
                ->setCellValue('E' . $i, $orderRequest->getFirstName())
                ->setCellValue('F' . $i, $orderRequest->getEmail())
                ->setCellValue('G' . $i, $orderRequest->getPhone())
                ->setCellValue('H' . $i, $orderRequest->getOrderStatus())
                ->setCellValue('I' . $i, $orderRequest->getNeed())
                ->setCellValue('J' . $i, $orderRequest->getGeneratedTurnover())
                ->setCellValue('K' . $i, $orderRequest->getDivision())
                ->setCellValue('L' . $i, $orderRequest->getPostalCode())
                ->setCellValue('M' . $i, $orderRequest->getAgency())
                ->setCellValue('N' . $i, $orderRequest->getSummary())
                ->setCellValue('O' . $i, $orderRequest->getFrequency())
                ->setCellValue('P' . $i, $orderRequest->getTonnage())
                ->setCellValue('Q' . $i, $orderRequest->getKookaburaNumber())
                ->setCellValue('R' . $i, $orderRequest->getDateCreation()->format('Y-m-d'));

            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Demandes-Devis-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/orderRequest/view/{id}", name="paprec_commercial_orderRequest_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, OrderRequest $orderRequest)
    {
        return $this->render('PaprecCommercialBundle:OrderRequest:view.html.twig', array(
            'orderRequest' => $orderRequest
        ));
    }

    /**
     * @Route("/orderRequest/edit/{id}", name="paprec_commercial_orderRequest_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, OrderRequest $orderRequest)
    {

        $status = array();
        foreach ($this->getParameter('paprec_order_status') as $s) {
            $status[$s] = $s;
        }

        $form = $this->createForm(OrderRequestEditType::class, $orderRequest, array(
            'status' => $status
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $orderRequest = $form->getData();
            $orderRequest->setDateUpdate(new \DateTime());

            if ($orderRequest->getAssociatedOrder() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de l'argument
                 */
                $associatedOrder = $orderRequest->getAssociatedOrder();
                $associatedOrderFileName = md5(uniqid()) . '.' . $associatedOrder->guessExtension();

                $associatedOrder->move($this->getParameter('paprec_commercial.order_request.files_path'), $associatedOrderFileName);

                $orderRequest->setAssociatedOrder($associatedOrderFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_commercial_orderRequest_view', array(
                'id' => $orderRequest->getId()
            ));

        }

        return $this->render('PaprecCommercialBundle:OrderRequest:edit.html.twig', array(
            'form' => $form->createView(),
            'orderRequest' => $orderRequest
        ));
    }

    /**
     * @Route("/orderRequest/remove/{id}", name="paprec_commercial_orderRequest_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, OrderRequest $orderRequest)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($orderRequest->getAttachedFiles() as $file) {
            $this->removeFile($this->getParameter('paprec_commercial.order_request.files_path') . '/' . $file);
            $orderRequest->setAttachedFiles();
        }
        if (!empty($orderRequest->getAssociatedOrder())) {
            $this->removeFile($this->getParameter('paprec_commercial.order_request.files_path') . '/' . $file);
            $orderRequest->setAssociatedOrder();
        }

        $orderRequest->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_commercial_orderRequest_index');
    }

    /**
     * @Route("/orderRequest/removeMany/{ids}", name="paprec_commercial_orderRequest_removeMany")
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
            $orderRequests = $em->getRepository('PaprecCommercialBundle:OrderRequest')->findById($ids);
            foreach ($orderRequests as $orderRequest) {
                foreach ($orderRequest->getAttachedFiles() as $file) {
                    $this->removeFile($this->getParameter('paprec_commercial.order_request.files_path') . '/' . $file);
                    $orderRequest->setAttachedFiles();
                }
                if (!empty($orderRequest->getAssociatedOrder())) {
                    $this->removeFile($this->getParameter('paprec_commercial.order_request.files_path') . '/' . $file);
                    $orderRequest->setAssociatedOrder();
                }
                $orderRequest->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_commercial_orderRequest_index');
    }


    /**
     * Supprimme un fichier du sytème de fichiers
     *
     * @param $path
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
     * @Route("/orderRequest/{id}/downloadAssociatedOrder", name="paprec_commercial_orderRequest_downloadAssociatedOrder")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function downloadAssociatedOrderAction( OrderRequest $orderRequest)
    {
        $filename = $orderRequest->getAssociatedOrder();
        $path = $this->getParameter('paprec_commercial.order_request.files_path');
        $content = file_get_contents($path . '/' . $filename);
        $extension = pathinfo($path . '/' . $filename, PATHINFO_EXTENSION);

        $response = new Response();
        $newFilename = "Demande-Devis-" . $orderRequest->getId() . '-Devis-Associe.' . $extension;

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $newFilename);

        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/orderRequest/{id}/downloadAttachedFiles", name="paprec_commercial_orderRequest_downloadAttachedFiles")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function downloadAttachedFilesAction(OrderRequest $orderRequest)
    {
        $path = $this->getParameter('paprec_commercial.order_request.files_path');
        $zipname = 'file.zip';
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $cpt = 1;
        foreach ($orderRequest->getAttachedFiles() as $file) {
            $extension = pathinfo($path . '/' . $file, PATHINFO_EXTENSION);
            $newFilename = "Demande-devis-" . $orderRequest->getId() . '-PJ' . $cpt . '.' . $extension;

            $filename= $path . '/' . $file;
            $zip->addFile($filename, $newFilename);
            $cpt++;
        }
        $zip->close();

        $name = $zipname;
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);
    }

}
