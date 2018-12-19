<?php

namespace Paprec\PublicBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WordpressController extends Controller
{


    /**
     * Get menu from WordPress API
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMenuFromWPAction($slug)
    {
        $client = new Client(['base_uri' => $this->getParameter('paprec_public_site_url')]);

        $response = $client->request('GET', '/wp-json/menus/v1/menus/' . $slug);

        $bodyResponse = json_decode($response->getBody(), true);

        if ($slug == 'shortlinks-menu') {
            foreach($bodyResponse['items'] as $item) {
                $shortlinks[] = array(
                    'id' => $item['ID'],
                    'title' => $item['title'],
                    'url' => $item['url']
                );
            }


            return $this->render('@PaprecPublic/Menu/shortlinksMenu.html.twig', array(
                'response' => $shortlinks
            ));
        } elseif ($slug == 'header-menu') {
            foreach($bodyResponse['items'] as $item) {
                $headers[] = array(
                    'id' => $item['ID'],
                    'title' => $item['title'],
                    'url' => $item['url']
                );
            }


            return $this->render('@PaprecPublic/Menu/headersMenu.html.twig', array(
                'response' => $headers
            ));
        } elseif ($slug == 'footer-menu') {
            foreach($bodyResponse['items'] as $item) {
                if ($item['menu_item_parent'] == '0') {
                    $footers[$item['ID']] = array(
                        'id' => $item['ID'],
                        'title' => $item['title'],
                        'url' => $item['url']
                    );
                } else {
                    $footers[$item['menu_item_parent']]['submenus'][] = array(
                        'id' => $item['ID'],
                        'title' => $item['title'],
                        'url' => $item['url']
                    );
                }
            }


            return $this->render('@PaprecPublic/Menu/footersMenu.html.twig', array(
                'response' => $footers
            ));
        } elseif ($slug == 'quicklinksmenu-footer') {
            foreach($bodyResponse['items'] as $item) {
                $quicklinksmenus[] = array(
                    'id' => $item['ID'],
                    'title' => $item['title'],
                    'url' => $item['url']
                );
            }


            return $this->render('@PaprecPublic/Menu/headersMenu.html.twig', array(
                'response' => $quicklinksmenus
            ));
        }

    }
}