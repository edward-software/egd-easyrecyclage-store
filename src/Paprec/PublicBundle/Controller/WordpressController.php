<?php

namespace Paprec\PublicBundle\Controller;

use GuzzleHttp\Client;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WordpressController extends Controller
{


    /**
     * Get menu from WordPress API
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMenuFromWPAction($slug)
    {
        try {

            $client = new Client(['base_uri' => $this->getParameter('paprec_public_site_url')]);

            $response = $client->request('GET', '/wp-json/menus/v1/menus/' . $slug);

            $bodyResponse = json_decode($response->getBody(), true);

            if ($slug == 'shortlinks-menu') {

                $shortlinks = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        $shortlinks[] = array(
                            'id' => $item['ID'],
                            'title' => $item['title'],
                            'url' => $item['url']
                        );
                    }
                }
                return $this->render('@PaprecPublic/Menu/shortlinksMenu.html.twig', array(
                    'items' => $shortlinks
                ));

            } elseif ($slug == 'header-menu') {

                $headers = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        if ($item['menu_item_parent'] != null && $item['menu_item_parent'] == '0') {
                            $headers[$item['ID']] = array(
                                'id' => $item['ID'],
                                'title' => $item['title'],
                                'url' => $item['url'],
                                'submenus' => array()
                            );
                        } elseif ($item['menu_item_parent'] !== null && $item['menu_item_parent'] != '') {
                            // teste si le numéro du parent est dans le premier niveau de menu
                            if (array_key_exists($item['menu_item_parent'], $headers)) {
                                $headers[$item['menu_item_parent']]['submenus'][$item['ID']] = array(
                                    'id' => $item['ID'],
                                    'title' => $item['title'],
                                    'url' => $item['url'],
                                    'submenus' => array()
                                );
                            } else {
                                foreach ($headers as $key => $menu) {
                                    if (isset($menu['submenus']) && is_array($menu['submenus']) && count($menu['submenus'])) {
                                        if (array_key_exists($item['menu_item_parent'], $menu['submenus'])) {
                                            $headers[$key]['submenus'][$item['menu_item_parent']]['submenus'][$item['ID']] = array(
                                                'id' => $item['ID'],
                                                'title' => $item['title'],
                                                'url' => $item['url']
                                            );
                                        }
                                    }
                                }
                            }

                        }

                    }
                }
                return $this->render('@PaprecPublic/Menu/headersMenu.html.twig', array(
                    'items' => $headers
                ));
            } elseif ($slug == 'footer-menu') {

                $footers = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        if ($item['menu_item_parent'] == '0') {
                            $footers[$item['ID']] = array(
                                'id' => $item['ID'],
                                'title' => $item['title'],
                                'url' => $item['url'],
                                'submenus' => array()
                            );
                        } else {
                            $footers[$item['menu_item_parent']]['submenus'][$item['ID']] = array(
                                'id' => $item['ID'],
                                'title' => $item['title'],
                                'url' => $item['url']
                            );
                        }
                    }
                }


                return $this->render('@PaprecPublic/Menu/footersMenu.html.twig', array(
                    'items' => $footers
                ));
            } elseif ($slug == 'quicklinksmenu-footer') {

                $quicklinksmenus = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        $quicklinksmenus[] = array(
                            'id' => $item['ID'],
                            'title' => $item['title'],
                            'url' => $item['url']
                        );
                    }
                }


                return $this->render('@PaprecPublic/Menu/quicklinksMenu.html.twig', array(
                    'items' => $quicklinksmenus
                ));
            }

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new Exception('cannotLoadMenuWorpress', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}