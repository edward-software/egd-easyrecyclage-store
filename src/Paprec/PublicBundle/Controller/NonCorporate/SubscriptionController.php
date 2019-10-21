<?php

namespace Paprec\PublicBundle\Controller\NonCorporate;


use Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate;
use Paprec\CommercialBundle\Form\QuoteRequestNonCorporate\QuoteRequestNonCorporateGroupeType;
use Paprec\CommercialBundle\Form\QuoteRequestNonCorporate\QuoteRequestNonCorporateShortType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{
    /**
     * @Route("/groupe/step0", name="paprec_public_non_corporate_subscription_groupe_step0")
     * @throws \Exception
     */
    public function groupeStep0Action(Request $request)
    {
        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');

        $quoteRequestNonCorporate = new QuoteRequestNonCorporate();
        $form = $this->createForm(QuoteRequestNonCorporateGroupeType::class, $quoteRequestNonCorporate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * On récupère le dossier contenat les fichiers joints
             *
             */
            $dir = $request->get('dirInput');

            $em = $this->getDoctrine()->getManager();

            $quoteRequestNonCorporate = $form->getData();
            $quoteRequestNonCorporate->setQuoteStatus('CREATED');
            $quoteRequestNonCorporate->setCustomerType('Groupe et Réseau');

            if ($dir) {
                $dirPath = $this->getParameter('paprec_uploaded_files_dir') . '/' . $dir;
                $filesUploaded = scandir($dirPath);

                $files = array();
                foreach ($filesUploaded as $uploadedFile) {
                    if ($uploadedFile !== '.' && $uploadedFile !== '..') {
                        if (!is_dir($this->getParameter('paprec_commercial.quote_request.files_path'))) {
                            mkdir($this->getParameter('paprec_commercial.quote_request.files_path'), 0755, true);
                        }
                        rename($dirPath . '/' . $uploadedFile, $this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $uploadedFile);
                        $files[] = $uploadedFile;
                    }
                }
                $quoteRequestNonCorporate->setAttachedFiles($files);
                rmdir($dirPath);
            }
            $em->persist($quoteRequestNonCorporate);
            $em->flush();

            $sendConfirmEmail = $quoteRequestNonCorporateManager->sendConfirmRequestEmail($quoteRequestNonCorporate);
            $sendNewRequestEmail = $quoteRequestNonCorporateManager->sendNewRequestEmail($quoteRequestNonCorporate);

            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_non_corporate_subscription_groupe_step1', array(
                    'quoteRequestId' => $quoteRequestNonCorporate->getId()
                ));
            }
        }


        return $this->render('@PaprecPublic/NonCorporate/groupeReseau.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/groupe/step1/{quoteRequestId}", name="paprec_public_non_corporate_subscription_groupe_step1")
     * @throws \Exception
     */
    public function groupeStep1Action(Request $request, $quoteRequestId)
    {
        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->find($quoteRequestId);
        return $this->render('@PaprecPublic/NonCorporate/groupReseauConfirm.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * @Route("/collectivite/step0", name="paprec_public_non_corporate_subscription_collectivite_step0")
     * @throws \Exception
     */
    public function collectiviteStep0Action(Request $request)
    {
        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');

        $quoteRequestNonCorporate = new QuoteRequestNonCorporate();
        $form = $this->createForm(QuoteRequestNonCorporateShortType::class, $quoteRequestNonCorporate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * On récupère le dossier contenat les fichiers joints
             */
            $dir = $request->get('dirInput');

            $em = $this->getDoctrine()->getManager();

            $quoteRequestNonCorporate = $form->getData();
            $quoteRequestNonCorporate->setQuoteStatus('CREATED');
            $quoteRequestNonCorporate->setCustomerType('Collectivité');

            if ($dir) {
                $dirPath = $this->getParameter('paprec_uploaded_files_dir') . '/' . $dir;
                $filesUploaded = scandir($dirPath);

                $files = array();
                foreach ($filesUploaded as $uploadedFile) {
                    if ($uploadedFile !== '.' && $uploadedFile !== '..') {
                        if (!is_dir($this->getParameter('paprec_commercial.quote_request.files_path'))) {
                            mkdir($this->getParameter('paprec_commercial.quote_request.files_path'), 0755, true);
                        }
                        rename($dirPath . '/' . $uploadedFile, $this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $uploadedFile);
                        $files[] = $uploadedFile;
                    }
                }
                $quoteRequestNonCorporate->setAttachedFiles($files);
                rmdir($dirPath);
            }
            $em->persist($quoteRequestNonCorporate);
            $em->flush();

            $sendConfirmEmail = $quoteRequestNonCorporateManager->sendConfirmRequestEmail($quoteRequestNonCorporate);
            $sendNewRequestEmail = $quoteRequestNonCorporateManager->sendNewRequestEmail($quoteRequestNonCorporate);

            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_non_corporate_subscription_collectivite_step1', array(
                    'quoteRequestId' => $quoteRequestNonCorporate->getId()
                ));
            }
        }

        return $this->render('@PaprecPublic/NonCorporate/collectivite.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/collectivite/step1/{quoteRequestId}", name="paprec_public_non_corporate_subscription_collectivite_step1")
     * @throws \Exception
     */
    public function collectiviteStep1Action(Request $request, $quoteRequestId)
    {
        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->find($quoteRequestId);
        return $this->render('@PaprecPublic/NonCorporate/collectiviteConfirm.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }

    /**
     * @Route("/particulier/step0", name="paprec_public_non_corporate_subscription_particulier_step0")
     * @throws \Exception
     */
    public function particulierStep0Action(Request $request)
    {
        $quoteRequestNonCorporateManager = $this->get('paprec_commercial.quote_request_non_corporate_manager');

        $quoteRequestNonCorporate = new QuoteRequestNonCorporate();
        $form = $this->createForm(QuoteRequestNonCorporateShortType::class, $quoteRequestNonCorporate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * On récupère le dossier contenat les fichiers joints
             */
            $dir = $request->get('dirInput');

            $em = $this->getDoctrine()->getManager();

            $quoteRequestNonCorporate = $form->getData();
            $quoteRequestNonCorporate->setQuoteStatus('CREATED');
            $quoteRequestNonCorporate->setCustomerType('Particulier');

            if ($dir) {
                $dirPath = $this->getParameter('paprec_uploaded_files_dir') . '/' . $dir;
                $filesUploaded = scandir($dirPath);

                $files = array();
                foreach ($filesUploaded as $uploadedFile) {
                    if ($uploadedFile !== '.' && $uploadedFile !== '..') {
                        if (!is_dir($this->getParameter('paprec_commercial.quote_request.files_path'))) {
                            mkdir($this->getParameter('paprec_commercial.quote_request.files_path'), 0755, true);
                        }
                        rename($dirPath . '/' . $uploadedFile, $this->getParameter('paprec_commercial.quote_request.files_path') . '/' . $uploadedFile);
                        $files[] = $uploadedFile;
                    }
                }
                $quoteRequestNonCorporate->setAttachedFiles($files);
                rmdir($dirPath);
            }
            $em->persist($quoteRequestNonCorporate);
            $em->flush();

            $sendConfirmEmail = $quoteRequestNonCorporateManager->sendConfirmRequestEmail($quoteRequestNonCorporate);
            $sendNewRequestEmail = $quoteRequestNonCorporateManager->sendNewRequestEmail($quoteRequestNonCorporate);

            if ($sendConfirmEmail && $sendNewRequestEmail) {
                return $this->redirectToRoute('paprec_public_non_corporate_subscription_particulier_step1', array(
                    'quoteRequestId' => $quoteRequestNonCorporate->getId()
                ));
            }
        }


        return $this->render('@PaprecPublic/NonCorporate/particulier.html.twig', array(
            'form' => $form->createView()
        ));
    }

// TODO SUPPRIMER FONCTION UNE FOIS TOUS LES MAILS OK

//    /**
//     * @Route("/testmail/{objectId}", name="paprec_public_non_corporate_subscription_test_mail")
//     * @throws \Exception
//     */
//    public function testMailAction(Request $request, $objectId)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $productD3EQuote = $em->getRepository('PaprecCommercialBundle:ProductD3EQuote')->find($objectId);
//        return $this->render('@PaprecCommercial/ProductD3EQuote/emails/sendNewQuoteEmail.html.twig', array(
//            'productD3EQuote' => $productD3EQuote
//        ));
//    }

    /**
     * @Route("/particulier/step1/{quoteRequestId}", name="paprec_public_non_corporate_subscription_particulier_step1")
     * @throws \Exception
     */
    public function particulierStep1Action(Request $request, $quoteRequestId)
    {
        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->find($quoteRequestId);
        return $this->render('@PaprecPublic/NonCorporate/particulierConfirm.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }
}
