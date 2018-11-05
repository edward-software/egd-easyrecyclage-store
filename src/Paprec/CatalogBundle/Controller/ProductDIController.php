<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\ProductDI;
use Paprec\CatalogBundle\Form\PictureProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Paprec\CatalogBundle\Form\ProductDIType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDIController extends Controller
{
    /**
     * @Route("/productDI", name="paprec_catalog_productDI_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:ProductDI:index.html.twig');
    }

    /**
     * @Route("/productDI/loadList", name="paprec_catalog_productDI_loadList")
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
        $cols['name'] = array('label' => 'name', 'id' => 'p.name', 'method' => array('getName'));
        $cols['capacity'] = array('label' => 'capacity', 'id' => 'p.capacity', 'method' => array('getCapacity'));
        $cols['dimensions'] = array('label' => 'dimensions', 'id' => 'p.dimensions', 'method' => array('getDimensions'));
        $cols['unitPrice'] = array('label' => 'unitPrice', 'id' => 'p.unitPrice', 'method' => array('getUnitPrice'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductDI', 'p')
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
                    $queryBuilder->expr()->like('p.dimensions', '?1'),
                    $queryBuilder->expr()->like('p.unitPrice', '?1')
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
     * @Route("/productDI/export",  name="paprec_catalog_productDI_export")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAction()
    {

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductDI', 'p')
            ->where('p.deleted IS NULL');

        $ProductDIs = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Produits DI")
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


        $phpExcelObject->getActiveSheet()->setTitle('Produits DI');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($ProductDIs as $productDI) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productDI->getId())
                ->setCellValue('B' . $i, $productDI->getName())
                ->setCellValue('C' . $i, $productDI->getDescription())
                ->setCellValue('D' . $i, $productDI->getCapacity())
                ->setCellValue('E' . $i, $productDI->getCapacityUnit())
                ->setCellValue('F' . $i, $productDI->getDimensions())
                ->setCellValue('G' . $i, $productDI->getReference())
                ->setCellValue('H' . $i, $productDI->getIsDisplayed())
                ->setCellValue('I' . $i, $productDI->getAvailablePostalCodes())
                ->setCellValue('J' . $i, $productDI->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Produits-DI-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productDI/view/{id}",  name="paprec_catalog_productDI_view")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Request $request, ProductDI $productDI)
    {
        if ($productDI->getDeleted() !== null) {
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


        return $this->render('PaprecCatalogBundle:ProductDI:view.html.twig', array(
            'productDI' => $productDI,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ));
    }

    /**
     * @Route("/productDI/add",  name="paprec_catalog_productDI_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $productDI = new ProductDI();

        $form = $this->createForm(ProductDIType::class, $productDI);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productDI = $form->getData();
            $productDI->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($productDI);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productDI_view', array(
                'id' => $productDI->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductDI:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productDI/edit/{id}",  name="paprec_catalog_productDI_edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, ProductDI $productDI)
    {
        if ($productDI->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(ProductDIType::class, $productDI);

        /**
         * On récupère les productDICategories présents avant la modif. Il faut les supprimer sinon on a un doublon
         */
        $currentPCs = $productDI->getProductDICategories();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDI = $form->getData();
            $productDI->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();

            /**
             * On supprime les anciennes relations ProductDIsCategories
             */
            foreach ($currentPCs as $pC) {
                $em->remove($pC);
            }

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productDI_view', array(
                'id' => $productDI->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductDI:edit.html.twig', array(
            'form' => $form->createView(),
            'productDI' => $productDI
        ));
    }

    /**
     * @Route("/productDI/remove/{id}", name="paprec_catalog_productDI_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Request $request, ProductDI $productDI)
    {
        $em = $this->getDoctrine()->getManager();

        /*
         * Suppression des images
         */
        foreach ($productDI->getPictures() as $picture) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
            $productDI->removePicture($picture);
        }

        $productDI->setDeleted(new \DateTime);
        $productDI->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productDI_index');
    }

    /**
     * @Route("/productDI/removeMany/{ids}", name="paprec_catalog_productDI_removeMany")
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
            $ProductDIs = $em->getRepository('PaprecCatalogBundle:ProductDI')->findById($ids);
            foreach ($ProductDIs as $productDI) {
                foreach ($productDI->getPictures() as $picture) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
                    $productDI->removePicture($picture);
                }

                $productDI->setDeleted(new \DateTime);
                $productDI->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_productDI_index');
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
     * @Route("/productDI/addPicture/{id}/{type}", name="paprec_catalog_productDI_addPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addPictureAction(Request $request, ProductDI $productDI) {

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
            $productDI->setDateUpdate(new \DateTime());
            $picture =  $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.di.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProductDI($productDI);
                $productDI->addPicture($picture);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productDI_view', array(
                'id' => $productDI->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductDI:view.html.twig', array(
            'productDI' => $productDI,
            'formAddPicture' => $form->createView()
        ));
    }

    /**
     * @Route("/productDI/editPicture/{id}/{pictureID}", name="paprec_catalog_productDI_editPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editPictureAction(Request $request, ProductDI $productDI) {

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
            $productDI->setDateUpdate(new \DateTime());
            $picture =  $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.di.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $oldPath);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productDI_view', array(
                'id' => $productDI->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductDI:view.html.twig', array(
            'productDI' => $productDI,
            'formEditPicture' => $form->createView()
        ));
    }


    /**
     * @Route("/productDI/removePicture/{id}/{pictureID}", name="paprec_catalog_productDI_removePicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removePictureAction(Request $request, ProductDI $productDI)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');

        $pictures = $productDI->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productDI->setDateUpdate(new \DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picture->getPath());
                $em->remove($picture);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productDI_view', array(
            'id' => $productDI->getId()
        ));
    }

    /**
 * @Route("/productDI/setPilotePicture/{id}/{pictureID}", name="paprec_catalog_productDI_setPilotePicture")
 * @Security("has_role('ROLE_ADMIN')")
 */
    public function setPilotPictureAction(Request $request, ProductDI $productDI)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productDI->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productDI->setDateUpdate(new \DateTime());
                $picture->setType('PilotPicture');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productDI_view', array(
            'id' => $productDI->getId()
        ));
    }

    /**
     * @Route("/productDI/setPicture/{id}/{pictureID}", name="paprec_catalog_productDI_setPicture")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setPictureAction(Request $request, ProductDI $productDI)
    {
        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');
        $pictures = $productDI->getPictures();
        foreach($pictures as $picture) {
            if ($picture->getId() == $pictureID) {
                $productDI->setDateUpdate(new \DateTime());
                $picture->setType('Picture');
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productDI_view', array(
            'id' => $productDI->getId()
        ));
    }
}
