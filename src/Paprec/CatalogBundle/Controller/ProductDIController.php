<?php

namespace Paprec\CatalogBundle\Controller;

use Paprec\CatalogBundle\Entity\ProductDI;
use Paprec\CatalogBundle\Entity\ProductDICategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

        $productsDI = $queryBuilder->getQuery()->getResult();

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
        foreach ($productsDI as $productDI) {

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
    public function viewAction(Request $request, ProductDI $product)
    {
        if ($product->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        return $this->render('PaprecCatalogBundle:ProductDI:view.html.twig', array(
            'productDI' => $product
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

            if ($productDI->getPicto() instanceof UploadedFile) {
                /**
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto" de le produit DI
                 */
                $picto = $productDI->getPicto();
                $pictoFileName = md5(uniqid()) . '.' . $picto->guessExtension();

                $picto->move($this->getParameter('paprec_catalog.productDI.picto_path'), $pictoFileName);

                $productDI->setPicto($pictoFileName);
            }

            $pictureAdded = array();
            foreach ($productDI->getPictures() as $picture) {
                if ($picture instanceof UploadedFile) {
                    $pictureFileName = md5(uniqid()) . '.' . $picture->guessExtension();
                    $picture->move($this->getParameter('paprec_catalog.productDI.picto_path'), $pictureFileName);
                    $pictureAdded[] = $pictureFileName;
                }
            }
            $productDI->setPictures($pictureAdded);

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
        if($productDI->getDeleted() !== null) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(ProductDIType::class, $productDI);

        $currentPicto = $productDI->getPicto();
        $currentPictures = $productDI->getPictures();
        /**
         * On récupère les productDICategories présents avant la modif. Il faut les supprimer sinon on a un doublon
         */
        $currentPCs = $productDI->getProductDICategories();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDI = $form->getData();
            $productDI->setDateUpdate(new \DateTime);
            $newPicto = $productDI->getPicto();

            if ($newPicto instanceof UploadedFile) {
                /**
                 * Si un nouveau pcito est sélectionné
                 * On place le picto uploadé dans le dossier web/uploads
                 * et on sauvegarde le nom du fichier dans la colonne 'picto' de la catégorie
                 */
                $pictoFileName = md5(uniqid()) . '.' . $newPicto->guessExtension();

                $newPicto->move($this->getParameter('paprec_catalog.category.picto_path'), $pictoFileName);

                $productDI->setPicto($pictoFileName);
            } else {
                /**
                 * Si pas de picto sélectionné, on remet le picto existant
                 */
                $productDI->setPicto($currentPicto);
            }

            if (!is_null($productDI->getPictures()) && !empty($productDI->getPictures())) {
                /**
                 * Si des photos ont été sélectionnées
                 * On place les photos uploadés dans le dossier web/uploads
                 * Et on sauvegarde tous les noms de fichier des photos dans la colonne 'pictures'
                 */
                $pictureAdded = array();
                foreach ($productDI->getPictures() as $picture) {
                    if ($picture instanceof UploadedFile) {
                        $pictureFileName = md5(uniqid()) . '.' . $picture->guessExtension();
                        $picture->move($this->getParameter('paprec_catalog.productDI.picto_path'), $pictureFileName);
                        $pictureAdded[] = $pictureFileName;
                    }
                }
                $productDI->setPictures($pictureAdded);
            } else {
                /**
                 * Si pas de photo sélectionné, on remet les photos d'avant
                 */
                $productDI->setPictures($currentPictures);
            }

            $em = $this->getDoctrine()->getManager();

            /**
             * On supprime les anciennes relations productsDICategories
             */
            foreach($currentPCs as $pC)
            {
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
            $productsDI = $em->getRepository('PaprecCatalogBundle:ProductDI')->findById($ids);
            foreach ($productsDI as $productDI) {
                $productDI->setDeleted(new \DateTime);
                $productDI->setIsDisplayed(false);
            }
            $em->flush();
        }

        return $this->redirectToRoute('paprec_catalog_productDI_index');
    }
}
