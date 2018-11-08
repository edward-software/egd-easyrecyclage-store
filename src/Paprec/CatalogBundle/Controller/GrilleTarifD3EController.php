<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\GrilleTarifD3E;
use Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E;
use Paprec\CatalogBundle\Form\GrilleTarifD3EType;
use Paprec\CatalogBundle\Form\GrilleTarifLigneD3EType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GrilleTarifD3EController extends Controller
{

    /**
     * @Route("/grilleTarifD3E",  name="paprec_catalog_grilleTarifD3E_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:GrilleTarifD3E:index.html.twig');
    }


    /**
     * @Route("/grilleTarifD3E/loadList",  name="paprec_catalog_grilleTarifD3E_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'g.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'g.name', 'method' => array('getName'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'g.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('g'))
            ->from('PaprecCatalogBundle:GrilleTarifD3E', 'g')
            ->where('g.deleted IS NULL');

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('g.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('g.name', '?1'),
                    $queryBuilder->expr()->like('g.dateCreation', '?1')
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
     * @Route("/grilleTarifD3E/export",  name="paprec_catalog_grilleTarifD3E_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction()
    {
        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('g'))
            ->from('PaprecCatalogBundle:GrilleTarifD3E', 'g')
            ->where('g.deleted IS NULL');

        $grilleTarifD3Es = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Grilles tarifaires D3E")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Texte')
            ->setCellValue('C1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Grilles tarifaires D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($grilleTarifD3Es as $grilleTarifD3E) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $grilleTarifD3E->getId())
                ->setCellValue('B' . $i, $grilleTarifD3E->getName())
                ->setCellValue('C' . $i, $grilleTarifD3E->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-GrilleTarifD3Es-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/grilleTarifD3E/view/{id}",  name="paprec_catalog_grilleTarifD3E_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, GrilleTarifD3E $grilleTarifD3E)
    {

        if ($grilleTarifD3E->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }
        $grilleTarifLigneD3E = new GrilleTarifLigneD3E();
        $addLigneForm = $this->createForm(GrilleTarifLigneD3EType::class, $grilleTarifLigneD3E);


        return $this->render('PaprecCatalogBundle:GrilleTarifD3E:view.html.twig', array(
            'grilleTarifD3E' => $grilleTarifD3E,
            'addLigneForm' => $addLigneForm->createView()
        ));
    }

    /**
     * @Route("/grilleTarifD3E/add",  name="paprec_catalog_grilleTarifD3E_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $grilleTarifD3E = new GrilleTarifD3E();

        $form = $this->createForm(GrilleTarifD3EType::class, $grilleTarifD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $grilleTarifD3E = $form->getData();
            $grilleTarifD3E->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($grilleTarifD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_view', array(
                'id' => $grilleTarifD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:GrilleTarifD3E:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/grilleTarifD3E/edit/{id}",  name="paprec_catalog_grilleTarifD3E_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, GrilleTarifD3E $grilleTarifD3E)
    {
        if ($grilleTarifD3E->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(GrilleTarifD3EType::class, $grilleTarifD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $grilleTarifD3E = $form->getData();
            $grilleTarifD3E->setDateUpdate(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_view', array(
                'id' => $grilleTarifD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:GrilleTarifD3E:edit.html.twig', array(
            'form' => $form->createView(),
            'grilleTarifD3E' => $grilleTarifD3E
        ));
    }

    /**
     * @Route("/grilleTarifD3E/remove/{id}", name="paprec_catalog_grilleTarifD3E_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, GrilleTarifD3E $grilleTarifD3E)
    {
        $em = $this->getDoctrine()->getManager();
        $grilleTarifD3E->setDeleted(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_index');
    }

    /**
     * @Route("/grilleTarifD3E/removeMany/{ids}", name="paprec_catalog_grilleTarifD3E_removeMany")
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
            $grilleTarifD3Es = $em->getRepository('PaprecCatalogBundle:GrilleTarifD3E')->findById($ids);
            foreach ($grilleTarifD3Es as $grilleTarifD3E) {
                $grilleTarifD3E->setDeleted(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_index');
    }

    /**
     * @Route("/grilleTarifD3E/addLigne/{id}", name="paprec_catalog_grilleTarifD3E_addLigne")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addLigneAction(Request $request, GrilleTarifD3E $grilleTarifD3E)
    {
        $grilleTarifLigneD3E = new GrilleTarifLigneD3E();
        $addLigneForm = $this->createForm(GrilleTarifLigneD3EType::class, $grilleTarifLigneD3E);

        $em = $this->getDoctrine()->getManager();
        $addLigneForm->handleRequest($request);
        if ($addLigneForm->isValid()) {
            $grilleTarifLigneD3E = $addLigneForm->getData();
            $grilleTarifLigneD3E->setGrilleTarifD3E($grilleTarifD3E);
            if ($grilleTarifLigneD3E->getMaxQuantity() == null) {
                $grilleTarifLigneD3E->setMaxQuantity($grilleTarifLigneD3E->getMinQuantity());
            }
            $grilleTarifD3E->addGrilleTarifLigneD3E($grilleTarifLigneD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_view', array(
                'id' => $grilleTarifD3E->getId()
            ));
        }

        return $this->render('PaprecCatalogBundle:GrilleTarifD3E:view.html.twig', array(
            'grilleTarifD3E' => $grilleTarifD3E,
            'addLigneForm' => $addLigneForm->createView()
        ));
    }

    /**
     * @Route("/grilleTarifD3E/removeLigne/{id}/{ligneID}", name="paprec_catalog_grilleTarifD3E_removeLigne")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removePictureAction(Request $request, GrilleTarifD3E $grilleTarifD3E)
    {

        $em = $this->getDoctrine()->getManager();

        $ligneID = $request->get('ligneID');

        $grilleTarifLigneD3Es = $grilleTarifD3E->getGrilleTarifLigneD3Es();
        foreach($grilleTarifLigneD3Es as $grilleTarifLigneD3E) {
            if ($grilleTarifLigneD3E->getId() == $ligneID) {
                $grilleTarifD3E->setDateUpdate(new \DateTime());
                $em->remove($grilleTarifLigneD3E);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_grilleTarifD3E_view', array(
            'id' => $grilleTarifD3E->getId()
        ));
    }
}
