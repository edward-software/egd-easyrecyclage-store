<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\Picture;
use Paprec\CatalogBundle\Entity\ProductD3E;
use Paprec\CatalogBundle\Form\PictureProductType;
use Paprec\CatalogBundle\Form\ProductD3EType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductD3EController extends Controller
{
    /**
     * @Route("/productD3E", name="paprec_catalog_productD3E_index")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function indexAction()
    {
        return $this->render('PaprecCatalogBundle:ProductD3E:index.html.twig');
    }

    /**
     * @Route("/productD3E/loadList", name="paprec_catalog_productD3E_loadList")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
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
        $cols['description'] = array('label' => 'description', 'id' => 'p.description', 'method' => array('getDescription'));
        $cols['position'] = array('label' => 'position', 'id' => 'p.position', 'method' => array('getPosition'));
        $cols['isDisplayed'] = array('label' => 'isDisplayed', 'id' => 'p.isDisplayed', 'method' => array('getIsDisplayed'));

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductD3E', 'p')
            ->where('p.deleted IS NULL');


        if (is_array($search) && isset($search['value']) && $search['value'] != '') {
            if (substr($search['value'], 0, 1) == '#') {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->eq('p.id', '?1')
                ))->setParameter(1, substr($search['value'], 1));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('p.name', '?1'),
                    $queryBuilder->expr()->like('p.description', '?1'),
                    $queryBuilder->expr()->like('p.position', '?1'),
                    $queryBuilder->expr()->like('p.isDisplayed', '?1')
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
     * @Route("/productD3E/export",  name="paprec_catalog_productD3E_export")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function exportAction()
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $translator = $this->container->get('translator');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder->select(array('p'))
            ->from('PaprecCatalogBundle:ProductD3E', 'p')
            ->where('p.deleted IS NULL');

        $productD3Es = $queryBuilder->getQuery()->getResult();

        $phpExcelObject->getProperties()->setCreator("Paprec Easy Recyclage")
            ->setLastModifiedBy("Paprec Easy Recyclage")
            ->setTitle("Paprec Easy Recyclage - Produits D3E")
            ->setSubject("Extraction");

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nom')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Coef. manutention')
            ->setCellValue('E1', 'Coef. relevé n° série')
            ->setCellValue('F1', 'Coef destruction')
            ->setCellValue('G1', 'Lien description')
            ->setCellValue('H1', 'Statut affichage')
            ->setCellValue('I1', 'Position')
            ->setCellValue('J1', 'Dispo géographique')
            ->setCellValue('K1', 'Date création');


        $phpExcelObject->getActiveSheet()->setTitle('Produits D3E');
        $phpExcelObject->setActiveSheetIndex(0);

        $i = 2;
        foreach ($productD3Es as $productD3E) {

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $productD3E->getId())
                ->setCellValue('B' . $i, $productD3E->getName())
                ->setCellValue('C' . $i, $productD3E->getDescription())
                ->setCellValue('D' . $i, $numberManager->denormalize($productD3E->getCoefHandling()))
                ->setCellValue('E' . $i, $numberManager->denormalize($productD3E->getCoefSerialNumberStmt()))
                ->setCellValue('F' . $i, $numberManager->denormalize($productD3E->getCoefDestruction()))
                ->setCellValue('G' . $i, $productD3E->getReference())
                ->setCellValue('H' . $i, $productD3E->getIsDisplayed())
                ->setCellValue('I' . $i, $productD3E->getPosition())
                ->setCellValue('J' . $i, $productD3E->getAvailablePostalCodes())
                ->setCellValue('K' . $i, $productD3E->getDateCreation()->format('Y-m-d'));
            $i++;
        }

        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        $fileName = 'PaprecEasyRecyclage-Extraction-Produits-D3E-' . date('Y-m-d') . '.xlsx';

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
     * @Route("/productD3E/view/{id}",  name="paprec_catalog_productD3E_view")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function viewAction(Request $request, ProductD3E $productD3E)
    {
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

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


        return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
            'productD3E' => $productD3E,
            'formAddPicture' => $formAddPicture->createView(),
            'formEditPicture' => $formEditPicture->createView()
        ));
    }

    /**
     * @Route("/productD3E/add",  name="paprec_catalog_productD3E_add")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $productD3E = new ProductD3E();

        $form = $this->createForm(ProductD3EType::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productD3E = $form->getData();

            $productD3E->setCoefHandling($numberManager->normalize($productD3E->getCoefHandling()));
            $productD3E->setCoefSerialNumberStmt($numberManager->normalize($productD3E->getCoefSerialNumberStmt()));
            $productD3E->setCoefDestruction($numberManager->normalize($productD3E->getCoefDestruction()));

            $productD3E->setDateCreation(new \DateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($productD3E);
            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));

        }

        return $this->render('PaprecCatalogBundle:ProductD3E:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3E/edit/{id}",  name="paprec_catalog_productD3E_edit")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, ProductD3E $productD3E)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');
        $productD3EManager = $this->get('paprec_catalog.product_d3e_manager');
        $productD3EManager->isDeleted($productD3E, true);

        $productD3E->setCoefHandling($numberManager->denormalize($productD3E->getCoefHandling()));
        $productD3E->setCoefSerialNumberStmt($numberManager->denormalize($productD3E->getCoefSerialNumberStmt()));
        $productD3E->setCoefDestruction($numberManager->denormalize($productD3E->getCoefDestruction()));
        $form = $this->createForm(ProductD3EType::class, $productD3E);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productD3E = $form->getData();

            $productD3E->setCoefHandling($numberManager->normalize($productD3E->getCoefHandling()));
            $productD3E->setCoefSerialNumberStmt($numberManager->normalize($productD3E->getCoefSerialNumberStmt()));
            $productD3E->setCoefDestruction($numberManager->normalize($productD3E->getCoefDestruction()));

            $productD3E->setDateUpdate(new \DateTime);

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductD3E:edit.html.twig', array(
            'form' => $form->createView(),
            'productD3E' => $productD3E
        ));
    }

    /**
     * @Route("/productD3E/remove/{id}", name="paprec_catalog_productD3E_remove")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removeAction(Request $request, ProductD3E $productD3E)
    {
        $em = $this->getDoctrine()->getManager();

        /*
         * Suppression des images
         */
        foreach ($productD3E->getPictos() as $picto) {
            $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picto->getPath());
            $productD3E->removePicture($picto);
        }

        $productD3E->setDeleted(new \DateTime);
        $productD3E->setIsDisplayed(false);
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productD3E_index');
    }

    /**
     * @Route("/productD3E/removeMany/{ids}", name="paprec_catalog_productD3E_removeMany")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
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
            $productD3Es = $em->getRepository('PaprecCatalogBundle:ProductD3E')->findById($ids);
            foreach ($productD3Es as $productD3E) {
                foreach ($productD3E->getPictos() as $picto) {
                    $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $picto->getPath());
                    $productD3E->removePicto($picto);
                }

                $productD3E->setDeleted(new \DateTime);
                $productD3E->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_productD3E_index');
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
     * @Route("/productD3E/addPicture/{id}/{type}", name="paprec_catalog_productD3E_addPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function addPictureAction(Request $request, ProductD3E $productD3E)
    {
        $picture = new Picture();
        foreach($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if($form->isValid())
        {
            $productD3E->setDateUpdate(new \DateTime());
            $picture =  $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.d3e.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $picture->setType($request->get('type'));
                $picture->setProductD3E($productD3E);
                $productD3E->addPicto($picture);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
            'productD3E' => $productD3E,
            'formAddPicture' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3E/editPicture/{id}/{pictureID}", name="paprec_catalog_productD3E_editPicture")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function editPictureAction(Request $request, ProductD3E $productD3E) {
        $em = $this->getDoctrine()->getManager();
        $pictureID = $request->get('pictureID');
        $picture = $em->getRepository('PaprecCatalogBundle:Picture')->find($pictureID);
        $oldPath = $picture->getPath();

        $em = $this->getDoctrine()->getManager();

        foreach($this->getParameter('paprec_types_picture') as $type) {
            $types[$type] = $type;
        }

        $form = $this->createForm(PictureProductType::class, $picture, array(
            'types' => $types
        ));


        $form->handleRequest($request);
        if($form->isValid())
        {
            $productD3E->setDateUpdate(new \DateTime());
            $picture =  $form->getData();

            if ($picture->getPath() instanceof UploadedFile) {
                $pic = $picture->getPath();
                $pictoFileName = md5(uniqid()) . '.' . $pic->guessExtension();

                $pic->move($this->getParameter('paprec_catalog.product.d3e.picto_path'), $pictoFileName);

                $picture->setPath($pictoFileName);
                $this->removeFile($this->getParameter('paprec_catalog.product.di.picto_path') . '/' . $oldPath);
                $em->flush();
            }

            return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
                'id' => $productD3E->getId()
            ));
        }
        return $this->render('PaprecCatalogBundle:ProductD3E:view.html.twig', array(
            'productD3E' => $productD3E,
            'formEditPicture' => $form->createView()
        ));
    }

    /**
     * @Route("/productD3E/removePicture/{id}/{pictureID}", name="paprec_catalog_productD3E_removePicture")
     * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_MANAGER_DIVISION') and 'D3E' in user.getDivisions())")
     */
    public function removePictureAction(Request $request, ProductD3E $productD3E)
    {

        $em = $this->getDoctrine()->getManager();

        $pictureID = $request->get('pictureID');

        $pictos = $productD3E->getPictos();
        foreach($pictos as $picto) {
            if ($picto->getId() == $pictureID) {
                $productD3E->setDateUpdate(new \DateTime());
                $this->removeFile($this->getParameter('paprec_catalog.product.d3e.picto_path') . '/' . $picto->getPath());
                $em->remove($picto);
                continue;
            }
        }
        $em->flush();

        return $this->redirectToRoute('paprec_catalog_productD3E_view', array(
            'id' => $productD3E->getId()
        ));
    }
}
