<?php

namespace Paprec\CatalogBundle\Controller;

use Goondi\ToolsBundle\Services\Logger;
use Paprec\CatalogBundle\Entity\Category;
use Paprec\CatalogBundle\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CategoryController extends Controller
{
    /**
     * @Route("/category/", name="paprec_catalog_category_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:Category:index.html.twig');
    }

    /**
     * @Route("/category/loadList", name="paprec_catalog_category_loadList")
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

        $cols['id'] = array('label' => 'id', 'id' => 'c.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'c.name', 'method' => array('getName'));
        $cols['description'] = array('label' => 'description', 'id' => 'c.description', 'method' => array('getDescription'));
        $cols['division'] = array('label' => 'division', 'id' => 'c.division', 'method' => array('getDivision'));
        $cols['position'] = array('label' => 'position', 'id' => 'c.position', 'method' => array('getPosition'));
        $cols['enabled'] = array('label' => 'enabled', 'id' => 'c.enabled', 'method' => array('getEnabled'));
        $cols['dateCreation'] = array('label' => 'dateCreation', 'id' => 'c.dateCreation', 'method' => array('getDateCreation'), 'filter' => array(array('name' => 'format', 'args' => array('Y-m-d H:i:s'))));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:Category', 'c')
            ->where('c.deleted IS NULL')
        ;

        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('c.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('c.name', '?1'),
                    $queryBuilder->expr()->like('c.description', '?1'),
                    $queryBuilder->expr()->like('c.division', '?1'),
                    $queryBuilder->expr()->like('c.position', '?1'),
                    $queryBuilder->expr()->like('c.dateCreation', '?1')
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
     * @Route("/category/export", name="paprec_catalog_category_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction(Request $request)
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('c'))
            ->from('PaprecCatalogBundle:Category', 'c')
            ->where('c.deleted IS NULL');

        $categories = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Utilisateurs")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Division')
            ->setCellValue('E1', 'Position')
            ->setCellValue('F1', 'Activé')
            ->setCellValue('G1', 'Date Création');

        $phpExcelObject->getActiveSheet()->setTitle('Catégories');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach($categories as $category) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $category->getId())
                ->setCellValue('B'.$i, $category->getName())
                ->setCellValue('C'.$i, $category->getDescription())
                ->setCellValue('D'.$i, $category->getDivision())
                ->setCellValue('E'.$i, $category->getPosition())
                ->setCellValue('F'.$i, $category->getEnabled())
                ->setCellValue('G'.$i, $category->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Categories-'.date('Y-m-d').'.xlsx';

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
     * @Route("/category/view/{id}", name="paprec_catalog_category_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, Category $category)
    {
        if($category->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        return $this->render('PaprecCatalogBundle:Category:view.html.twig', array(
            'category' => $category
        ));
    }

    /**
     * @Route("/category/add", name="paprec_catalog_catergory_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {

        $category = new Category();

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(CategoryType::class, $category, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setDateCreation(new \DateTime);

            if($category->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto" de la catégorie
                 */
                $picto = $category->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.category.picto_path'), $pictoFileName);

                $category->setPicto($pictoFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_category_view', array(
                'id' => $category->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Category:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/category/enableMany/{ids}", name="paprec_catalog_category_enableMany")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function enableManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if(! $ids) {
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if(is_array($ids) && count($ids)) {
            $categories = $em->getRepository('PaprecCatalogBundle:Category')->findById($ids);
            foreach ($categories as $category){
                $category->setEnabled(true);
                $category->setDateUpdate(new \DateTime);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_category_index');

    }


    /**
     * @Route("/category/edit/{id}", name="paprec_catalog_category_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Category $category)
    {
        if($category->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $divisions = array();
        foreach($this->getParameter('paprec_divisions') as $division) {
            $divisions[$division] = $division;
        }

        $form = $this->createForm(CategoryType::class, $category, array(
            'division' => $divisions
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category = $form->getData();
            $category->setDateUpdate(new \DateTime);

            if($category->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de la catégorie
                 */
                $picto = $category->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.category.picto_path'), $pictoFileName);

                $category->setPicto($pictoFileName);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_category_view', array(
                'id' => $category->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:Category:edit.html.twig', array(
            'form' => $form->createView(),
            'category' => $category
        ));
    }

    /**
     * @Route("/category/remove/{id}", name="paprec_catalog_category_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();

        $category->setDeleted(new \DateTime);
        $category->setEnabled(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_category_index');
    }

    /**
     * @Route("/category/removeMany/{ids}", name="paprec_catalog_category_removeMany")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeManyAction(Request $request)
    {
        $ids = $request->get('ids');

        if(! $ids) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $ids = explode(',', $ids);

        if(is_array($ids) && count($ids)) {
            $categories = $em->getRepository('PaprecCatalogBundle:Category')->findById($ids);
            foreach ($categories as $category){
                $category->setDeleted(new \DateTime);
                $category->setEnabled(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_category_index');
    }

}
