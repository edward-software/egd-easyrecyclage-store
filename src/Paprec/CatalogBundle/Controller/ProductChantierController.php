<?php

namespace Paprec\CatalogBundle\Controller;

use Exception;
use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\ProductChantier;
use Paprec\CatalogBundle\Entity\ProductChantierCategory;
use Paprec\CatalogBundle\Form\PictureProductType;
use Paprec\CatalogBundle\Form\ProductChantierCategoryAddType;
use Paprec\CatalogBundle\Form\ProductChantierCategoryEditType;
use Paprec\CatalogBundle\Form\ProductChantierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductChantierController extends Controller
{
    /**
     * @Route("/productChantier", name="paprec_catalog_productChantier_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:ProductChantier:index.html.twig');
    }

    /**
     * @Route("/productChantier/loadList", name="paprec_catalog_productChantier_loadList")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function loadListAction(Request $request)
    {
        $return = array();

        $filters = $request->get('filters');
        $pageSize = $request->get('length');
        $start = $request->get('start');
        $quotes = $request->get('quote');
        $search = $request->get('search');
        $columns = $request->get('columns');

        $cols['id'] = array('label' => 'id', 'id' => 'p.id', 'method' => array('getId'));
        $cols['name'] = array('label' => 'name', 'id' => 'p.name', 'method' => array('getName'));
        $cols['capacity'] = array('label' => 'capacity', 'id' => 'p.capacity', 'method' => array('getCapacity'));
        $cols['dimensions'] = array('label' => 'dimensions', 'id' => 'p.dimensions', 'method' => array('getDimensions'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductChantier', 'p')
            ->where('p.deleted IS NULL');


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.name', '?1'),
                    $queryBuilder->expr()->like('p.capacity', '?1'),
                    $queryBuilder->expr()->like('p.dimensions', '?1')
                ))->setParameter(1, '%' . $search['value'] . '%');
            }
        }

        $datatable = $this->get('goondi_tools.datatable')->generateTable($cols, $queryBuilder, $pageSize, $start, $quotes, $columns, $filters);

        $return['recordsTotal'] = $datatable['recordsTotal'];
        $return['recordsFiltered'] = $datatable['recordsTotal'];
        $return['data'] = $datatable['data'];
        $return['resultCode'] = 1;
        $return['resultDescription'] = "success";

        return new JsonResponse($return);

    }

    /**
     * @Route("/productChantier/export",  name="paprec_catalog_productChantier_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction()
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductChantier', 'p')
            ->where('p.deleted IS NULL');

        $productsChantier = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Produits Chantier")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Volume')
            ->setCellValue('E1', 'Unité Vol')
            ->setCellValue('F1', 'Dimensions')
            ->setCellValue('G1', 'Lien description')
            ->setCellValue('H1', 'Statut affichage')
            ->setCellValue('I1', 'Dispo géographique')
            ->setCellValue('J1', 'Date création');


        $phpExcelObject->getActiveSheet()->setTitle('Produits Chantier');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productsChantier as $productChantier) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productChantier->getId())
                ->setCellValue('B' . $i, $productChantier->getName())
                ->setCellValue('C' . $i, $productChantier->getDescription())
                ->setCellValue('D' . $i, $productChantier->getCapacity())
                ->setCellValue('E' . $i, $productChantier->getCapacityUnit())
                ->setCellValue('F' . $i, $productChantier->getDimensions())
                ->setCellValue('G' . $i, $productChantier->getReference())
                ->setCellValue('H' . $i, $productChantier->getIsDisplayed())
                ->setCellValue('I' . $i, $productChantier->getAvailablePostalCodes())
                ->setCellValue('J' . $i, $productChantier->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Produits-Chantier-' . date('Y-m-d') . '.xlsx';

        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::ChantierSPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/productChantier/view/{id}",  name="paprec_catalog_productChantier_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, ProductChantier $product)
    {
        if ($product->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }
        foreach($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $picture = new Picture();

        $formAddPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $formEditPicture = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));


        return $this->render('PaprecCatalogBundle:ProductChantier:view.html.twig', array(
            'productChantier' => $product,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ));
    }

    /**
     * @Route("/productChantier/add",  name="paprec_catalog_productChantier_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $productChantier = new ProductChantier();

        $form = $this->createForm(ProductChantierType::class, $productChantier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productChantier = $form->getData();
            $productChantier->setDateCreation(new \DateTime);


            $em = $this->getDoctrine()->getManager();
            $em->persist($productChantier);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductChantier:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productChantier/edit/{id}",  name="paprec_catalog_productChantier_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, ProductChantier $productChantier)
    {
        if ($productChantier->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(ProductChantierType::class, $productChantier);

        /**
         * On récupère les productChantierCategories présents avant la modif. Il faut les supprimer sinon on a un doublon
         */
        $currentPCs = $productChantier->getProductChantierCategories();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productChantier = $form->getData();
            $productChantier->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();

            /**
             * On supprime les anciennes relations productsChantierCategories
             */
            foreach ($currentPCs as $pC) {
                $em->remove($pC);
            }

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductChantier:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantier' => $productChantier
        ));
    }

    /**
     * @Route("/productChantier/remove/{id}", name="paprec_catalog_productChantier_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, ProductChantier $productChantier)
    {
        $em = $this->getDoctrine()->getManager();

        /*
        * Suppression des images
        */
        foreach ($productChantier->getPictures() as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.chantier.picto_path') . '/' . $picture->getPath());
            $productChantier->removePicture($picture);
        }

        $productChantier->setDeleted(new \DateTime);
        $productChantier->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productChantier_index');
    }

    /**
     * @Route("/productChantier/removeMany/{ids}", name="paprec_catalog_productChantier_removeMany")
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
            $productsChantier = $em->getRepository('PaprecCatalogBundle:ProductChantier')->findById($ids);
            foreach ($productsChantier as $productChantier) {
                /*
                * Suppression des images
                */
                foreach ($productChantier->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.chantier.picto_path') . '/' . $picture->getPath());
                    $productChantier->removePicture($picture);
                }

                $productChantier->setDeleted(new \DateTime);
                $productChantier->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_productChantier_index');
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
     * @Route("/productChantier/addPicture/{id}/{type}", name="paprec_catalog_productChantier_addPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addPictureAction(Request $request, ProductChantier $productChantier) {

        $picture = new Picture();
        foreach($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $em = $this->getDoctrine()->getEntityManager();

        $form->handleRequest($request);
        if($form->isValid())
        {
            $picture =  $form->getData();
            $productChantier->setDateUpdate(new \DateTime());

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.di.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProductChantier($productChantier);
                $productChantier->addPicture($picture);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductChantier:view.html.twig', array(
            'productChantier' => $productChantier,
            'formAddPicture' => $form->createView()
        ));
    }

    /**
     * @Route("/productChantier/editPicture/{id}/{pictureID}", name="paprec_catalog_productChantier_editPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editPictureAction(Request $request, ProductChantier $productChantier) {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $picture = $em->getRepository('PaprecCatalogBundle:Picture')->find($pictureID);
        $oldPath = $picture->getPath();

        $em = $this->getDoctrine()->getEntityManager();

        foreach($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));


        $form->handleRequest($request);
        if($form->isValid())
        {
            $picture =  $form->getData();
            $productChantier->setDateUpdate(new \DateTime());

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.di.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $oldPath);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductChantier:view.html.twig', array(
            'productChantier' => $productChantier,
            'formEditPicture' => $form->createView()
        ));
    }


    /**
     * @Route("/productChantier/removePicture/{id}/{pictureID}", name="paprec_catalog_productChantier_removePicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removePictureAction(Request $request, ProductChantier $productChantier)
    {


        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');

        $pictures = $productChantier->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productChantier->setDateUpdate(new \DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
            'id' => $productChantier->getId()
        ));
    }

    /**
     * @Route("/productChantier/setPilotePicture/{id}/{pictureID}", name="paprec_catalog_productChantier_setPilotePicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setPilotPictureAction(Request $request, ProductChantier $productChantier)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productChantier->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productChantier->setDateUpdate(new \DateTime());
                $picture->setType('PilotPicture');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
            'id' => $productChantier->getId()
        ));
    }

    /**
     * @Route("/productChantier/setPicture/{id}/{pictureID}", name="paprec_catalog_productChantier_setPicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setPictureAction(Request $request, ProductChantier $productChantier)
    {
        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productChantier->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productChantier->setDateUpdate(new \DateTime());
                $picture->setType('Picture');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
            'id' => $productChantier->getId()
        ));
    }


    /**
     * @Route("/productChantier/{id}/addCategory", name="paprec_catalog_productChantier_addCategory")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addCategoryAction(Request $request, ProductChantier $productChantier)
    {

        $em = $this->getDoctrine()->getManager();
        $productChantierCategoryRepo = $em->getRepository('PaprecCatalogBundle:ProductChantierCategory');

        $submitForm = $request->get('submitForm');

        if ($productChantier->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $productChantierCategory = new ProductChantierCategory();

        $form = $this->createForm(ProductChantierCategoryAddType::class, $productChantierCategory,
            array(
                'productId' => $productChantier->getId(),
                'productChantierCategoryRepo' => $productChantierCategoryRepo
            ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productChantierCategory = $form->getData();
            $productChantierCategory->setProductChantier($productChantier);
            $productChantier->addProductChantierCategory($productChantierCategory);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductChantierCategory:add.html.twig', array(
            'form' => $form->createView(),
            'productChantier' => $productChantier,
        ));
    }

    /**
     * @Route("/productChantier/{id}/editCategory/{productChantierCategoryId}", name="paprec_catalog_productChantier_editCategory")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productChantier", options={"id" = "id"})
     * @ParamConverter("productChantierCategory", options={"id" = "productChantierCategoryId"})
     */
    public function editCategoryAction(Request $request, ProductChantier $productChantier, ProductChantierCategory $productChantierCategory)
    {
        $em = $this->getDoctrine()->getManager();

        if ($productChantier->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierCategory->getProductChantier() !== $productChantier) {
            throw new NotFoundHttpException();
        }


        $form = $this->createForm(ProductChantierCategoryEditType::class, $productChantierCategory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productChantierCategory = $form->getData();
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
                'id' => $productChantier->getId()
            ));
        }

        return $this->render('PaprecCatalogBundle:ProductChantierCategory:edit.html.twig', array(
            'form' => $form->createView(),
            'productChantier' => $productChantier,
            'productChantierCategory' => $productChantierCategory
        ));
    }

    /**
     * @Route("/productChantier/{id}/removeCategory/{productChantierCategoryId}", name="paprec_catalog_productChantier_removeCategory")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("productChantier", options={"id" = "id"})
     * @ParamConverter("productChantierCategory", options={"id" = "productChantierCategoryId"})
     */
    public function removeLineAction(Request $request, ProductChantier $productChantier, ProductChantierCategory $productChantierCategory)
    {
        if ($productChantier->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        if ($productChantierCategory->getProductChantier() !== $productChantier) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($productChantierCategory);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productChantier_view', array(
            'id' => $productChantier->getId()
        ));
    }
}
