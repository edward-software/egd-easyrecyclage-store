<?php

namespace Paprec\PublicBundle\Controller;

use Paprec\CommercialBundle\Entity\QuoteRequest;
use Paprec\CommercialBundle\Form\QuoteRequestShortType;
use Paprec\PublicBundle\Entity\Cart;
use Paprec\PublicBundle\Service\CartManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    /**
     * @Route("/step0", name="paprec_public_corp_home_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $location = $request->get('l');
        $division = $request->get('d');
        $frequency = $request->get('f');

        $cartManager = $this->get('paprec.cart_manager');

        /**
         * step définie le prochain champ à afficher
         * Par défaut on est à la step l (location)
         * Quand l est définie on passe à l'étape d puis f
         * si on choisit "Régulier", on passe en étape r
         */
        $step = "l";
        $divisions = $this->getParameter('paprec_divisions');
        if (isset($location)) {
            $step = "d";
        }
        if (isset($location) && isset($division) && !empty($division)) {
            $step = "f";
        }
        if (isset($location) && isset($division) && isset($frequency) && !empty($frequency)) {

            // On créé un Cart qui va porter les informations saisies et que l'on va passer aux SubscriptionControllers

            $cart = $cartManager->add($location, $division, $frequency);


            if ($cart->getFrequency() == 'regular') {
                $step = "r";
                // Si  la personne choisi "Régulier", on lui créé quand même un Cart
                // On renvoit ce Cart au twig, ainsi la personne peut "Remplir un formulaire" et abandonner le Cart
                // Ou bien "d'estimer son besoin en 3 minutes" et on navigue vers la step1 en passant le Cart
                return $this->render('@PaprecPublic/Shared/Home/index.html.twig', array(
                    'divisions' => $divisions,
                    'step' => $step,
                    'cart' => $cart
                ));
            } else {
                switch ($cart->getDivision()) {
                    case('DI'):
                        return $this->redirectToRoute('paprec_public_corp_DI_subscription_step1', array(
                            'cartUuid' => $cart->getId()
                        ));
                        break;
                    case('CHANTIER'):
                        return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step0', array(
                            'cartUuid' => $cart->getId()
                        ));
                    case('D3E'):
                        return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step0', array(
                            'cartUuid' => $cart->getId()
                        ));
                }
            }

        }

        return $this->render('@PaprecPublic/Shared/Home/index.html.twig', array(
            'divisions' => $divisions,
            'step' => $step
        ));
    }

    /**
     * Formulaire pour besoin Régulier : commun à toutes les divisions donc dans HomeController
     * @Route("/regularForm/{cartUuid}", name="paprec_public_home_regularForm")
     * @param Request $request
     * @throws \Exception
     */
    public function regularFormAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $divisions = $this->getParameter('paprec_divisions');
        $cart = $cartManager->get($cartUuid);

        $quoteRequest = new QuoteRequest();
        $form = $this->createForm(QuoteRequestShortType::class, $quoteRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $quoteRequest = $form->getData();
            $quoteRequest->setQuoteStatus('Créé');
            $quoteRequest->setFrequency($cart->getFrequency());
            $quoteRequest->setDivision($cart->getDivision());
            $quoteRequest->setPostalCode(substr($cart->getLocation(), 0, 5));

            $files = array();
            foreach ($quoteRequest->getAttachedFiles() as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    /**
                     * On place le file uploadé dans le dossier web/files
                     * et on ajoute le nom du fichier md5 dans le tableau $files
                     */
                    $uploadedFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();

                    $uploadedFile->move($this->getParameter('paprec_commercial.quote_request.files_path'), $uploadedFileName);
                    $files[] = $uploadedFileName;
                }
            }
            $quoteRequest->setAttachedFiles($files);
            $em->persist($quoteRequest);
            $em->flush();

            return $this->redirectToRoute('paprec_public_home_regularConfirm', array(
                'cartUuid' => $cart->getId(),
                'quoteRequestId' => $quoteRequest->getId()
            ));
        }
        return $this->render('@PaprecPublic/Shared/regularForm.html.twig', array(
            'form' => $form->createView(),
            'cart' => $cart,
            'divisions' => $divisions
        ));
    }

    /**
     * Formulaire pour besoin Régulier : commun à toutes les divisions donc dans HomeController
     * @Route("/regularConfirm/{cartUuid}/{quoteRequestId}", name="paprec_public_home_regularConfirm")
     * @param Request $request
     * @throws \Exception
     */
    public function regularConfirmAction(Request $request, $cartUuid, $quoteRequestId)
    {
        $em = $this->getDoctrine()->getManager();
        $quoteRequest = $em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($quoteRequestId);
        return $this->render('@PaprecPublic/Shared/regularConfirm.html.twig', array(
            'quoteRequest' => $quoteRequest
        ));
    }
}
