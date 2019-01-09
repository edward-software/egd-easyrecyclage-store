<?php

namespace Paprec\CommercialBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatsController extends Controller
{
    /**
     * @Route("/stats", name="paprec_commercial_stats_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $totalQuoteStatus = array();
        $totalOrderStatus = array();

        /**
         * Récupération des statuts possibles des devis et commandes
         */
        $quoteStatusList = $this->getParameter('paprec_quote_status');
        $orderStatusList = $this->getParameter('paprec_order_status');

        /**
         * Calcul des totaux de chaque tableau qu'importe le statut
         */
        $totalQuoteStatus['DI']['total'] = $this->getQuoteStats('DI');
        $totalQuoteStatus['CHANTIER']['total'] = $this->getQuoteStats('Chantier');
        $totalQuoteStatus['D3E']['total'] = $this->getQuoteStats('D3E');
        $totalOrderStatus['CHANTIER']['total'] = $this->getOrderStats('Chantier');
        $totalOrderStatus['D3E']['total'] = $this->getOrderStats('D3E');


        if (is_array($quoteStatusList) && count($quoteStatusList)) {
            foreach ($quoteStatusList as $status) {
                $totalQuoteStatus['DI'][$status] = $this->getQuoteStats('DI', $totalQuoteStatus['DI']['total'], $status);
                $totalQuoteStatus['CHANTIER'][$status] = $this->getQuoteStats('Chantier', $totalQuoteStatus['CHANTIER']['total'], $status);
                $totalQuoteStatus['D3E'][$status] = $this->getQuoteStats('D3E', $totalQuoteStatus['D3E']['total'], $status);
            }
        }

        if (is_array($orderStatusList) && count($orderStatusList)) {
            foreach ($orderStatusList as $status) {
                $totalOrderStatus['CHANTIER'][$status] = $this->getOrderStats('Chantier', $totalOrderStatus['CHANTIER']['total'], $status);
                $totalOrderStatus['D3E'][$status] = $this->getOrderStats('D3E', $totalOrderStatus['D3E']['total'], $status);
            }
        }

        return $this->render('@PaprecCommercial/Stats/index.html.twig', array(
            'quoteStatusList' => $quoteStatusList,
            'orderStatusList' => $orderStatusList,
            'totalQuoteStatus' => $totalQuoteStatus,
            'totalOrderStatus' => $totalOrderStatus
        ));
    }

    /**
     * Fonction retournant dans un tableau les stats sur les devis en fonction de la division et du statut
     * Le tableau retourné correspond à une colonne d'un tableau dans le HTML
     * Si on ne renseigne pas de $total et de $status, alors la fonction retourne les stats totales sur les devis de la division qu'importe le statut du devis
     *
     * @param $division
     * @param null $total
     * @param null $status
     * @return array
     */
    private function getQuoteStats($division, $total = null, $status = null)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $quoteStats = array();
        /**
         * Nombre de devis
         */
        $sql = "SELECT COUNT(*) as nbQuote FROM product" . $division . "Quotes p WHERE p.deleted IS NULL";
        if ($status != null) {
            $sql .= " AND p.quoteStatus = '" . $status . "'";
        }
        $result = $this->executeSQL($sql);
        $quoteStats['nbQuote'] = $result[0]['nbQuote'];

        /**
         * Montant total
         */
        $sql = "SELECT SUM(COALESCE(p.totalAmount, 0)) as totalAmount FROM product" . $division . "Quotes p WHERE p.deleted IS NULL";
        if ($status != null) {
            $sql .= " AND p.quoteStatus = '" . $status . "'";
        }
        $result = $this->executeSQL($sql);
        $quoteStats['totalAmountFloat'] = $numberManager->denormalize($result[0]['totalAmount']);
        $quoteStats['totalAmount'] = number_format($numberManager->denormalize($result[0]['totalAmount']), 2, ',', ' ');

        /**
         * Montant total moyen
         */
        if ($quoteStats['nbQuote']) {
            $quoteStats['averageTotalAmount'] = number_format($quoteStats['totalAmountFloat'] / $quoteStats['nbQuote'], 2, ',', ' ');
        }

        /**
         * CA généré
         */
        $sql = "SELECT SUM(COALESCE(p.generatedTurnover, 0)) as generatedTurnover FROM product" . $division . "Quotes p WHERE p.deleted IS NULL";
        if ($status != null) {
            $sql .= " AND p.quoteStatus = '" . $status . "'";
        }
        $result = $this->executeSQL($sql);
        $quoteStats['generatedTurnoverFloat'] = $numberManager->denormalize($result[0]['generatedTurnover']);
        $quoteStats['generatedTurnover'] = number_format($numberManager->denormalize($result[0]['generatedTurnover']), 2, ',', ' ');

        /**
         * CA généré moyen
         */
        if ($quoteStats['nbQuote']) {
            $quoteStats['averageGeneratedTurnover'] = number_format($quoteStats['generatedTurnoverFloat'] / $quoteStats['nbQuote'], 2, ',', ' ');
        }

        /**
         * % en nombre
         */
        $quoteStats['percentByNumber'] = 0;
        if ($quoteStats['nbQuote']) {
            if ($total['nbQuote']) {
                if ($total['nbQuote'] !== 0) {
                    $quoteStats['percentByNumber'] = number_format(round($quoteStats['nbQuote'] * 100 / $total['nbQuote'], 2), 2, ',', ' ');
                }
            } else {
                $quoteStats['percentByNumber'] = 100;
            }
        }

        /**
         * % en CA
         */
        $quoteStats['percentByTurnover'] = 0;
        if ($quoteStats['generatedTurnoverFloat']) {
            if ($total['generatedTurnoverFloat']) {
                if ($total['generatedTurnoverFloat'] !== 0) {
                    $quoteStats['percentByTurnover'] = number_format(round($quoteStats['generatedTurnoverFloat'] * 100 / $total['generatedTurnoverFloat'], 2), 2, ',', ' ');
                }
            } else {
                $quoteStats['percentByTurnover'] = 100;
            }
        }

        return $quoteStats;

    }


    /**
     *
     * Fonction retournant dans un tableau les stats sur les commandes en fonction de la division et du statut
     * Le tableau retourné correspond à une colonne d'un tableau dans le HTML
     * Si on ne renseigne pas de $total et de $status, alors la fonction retourne les stats totales sur les commandes de la division qu'importe le statut de celles-ci
     *
     * @param $division
     * @param null $total
     * @param null $status
     * @return array
     */
    private function getOrderStats($division, $total = null, $status = null)
    {
        $numberManager = $this->get('paprec_catalog.number_manager');

        $orderStats = array();
        /**
         * Nombre de commandes
         */
        $sql = "SELECT COUNT(*) as nbOrder FROM product" . $division . "Orders p WHERE p.deleted IS NULL";
        if ($status != null) {
            $sql .= " AND p.orderStatus = '" . $status . "'";
        }
        $result = $this->executeSQL($sql);
        $orderStats['nbOrder'] = $result[0]['nbOrder'];

        /**
         * Montant total
         */
        $sql = "SELECT SUM(COALESCE(p.totalAmount, 0)) as totalAmount FROM product" . $division . "Orders p WHERE p.deleted IS NULL";
        if ($status != null) {
            $sql .= " AND p.orderStatus = '" . $status . "'";
        }
        $result = $this->executeSQL($sql);
        $orderStats['totalAmountFloat'] = $numberManager->denormalize($result[0]['totalAmount']);
        $orderStats['totalAmount'] = number_format($numberManager->denormalize($result[0]['totalAmount']), 2, ',', ' ');

        /**
         * Montant total moyen
         */
        if ($orderStats['nbOrder']) {
            $orderStats['averageTotalAmount'] = number_format($orderStats['totalAmountFloat'] / $orderStats['nbOrder'], 2, ',', ' ');
        }

        /**
         * % en nombre
         */
        $orderStats['percentByNumber'] = 0;
        if ($orderStats['nbOrder']) {
            if ($total['nbOrder']) {
                if ($total['nbOrder'] !== 0) {
                    $orderStats['percentByNumber'] = number_format(round($orderStats['nbOrder'] * 100 / $total['nbOrder'], 2), 2, ',', ' ');
                }
            } else {
                $orderStats['percentByNumber'] = 100;
            }
        }

        /**
         * % en CA
         */
        $orderStats['percentByTurnover'] = 0;
        if ($orderStats['totalAmountFloat']) {
            if ($total['totalAmountFloat']) {
                if ($total['totalAmountFloat'] !== 0) {
                    $orderStats['percentByTurnover'] = number_format(round($orderStats['totalAmountFloat'] * 100 / $total['totalAmountFloat'], 2), 2, ',', ' ');
                }
            } else {
                $orderStats['percentByTurnover'] = 100;
            }
        }

        return $orderStats;
    }

    /**
     * Execute une requete SQL avec le connecteur PDO et retourne les résultats dans un tableau
     *
     * @param $sql
     * @return mixed
     */
    private function executeSQL($sql)
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $result = $stmt->fetchAll();
    }


}
