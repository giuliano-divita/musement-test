<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Sitemap extends AbstractController
{
    /**
     * @Route(
     *     "/{_locale}/sitemap",
     *     defaults={
     *         "_locale": "it",
     *         "_format" : "xml"
     *     },
     *     requirements={
     *         "_locale": "es|fr|it",
     *     }
     * )
     * @param $_locale
     * @return Response
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function indexAction($_locale)
    {
        $urls = [];
        $httpClient = HttpClient::create(['headers' => [
            'Accept-Language' => "$_locale-".strtoupper($_locale),
        ]]);
        $response = $httpClient->request('GET', 'https://api.musement.com/api/v3/cities?limit=20');

        $cities = json_decode($response->getContent(), true);

        foreach ($cities as $city){
            $urls[] = ['loc' => $city['url'], 'priority' => .7];
            $response = $httpClient->request('GET',"https://api.musement.com/api/v3/cities/$city[id]/activities?limit=20");

            $activities = json_decode($response->getContent(), true);

            foreach ($activities['data'] as $activity){
                $urls[] = ['loc' => $activity['url'], 'priority' => .5];
            }
        }

        return $this->render('sitemap/index.xml.twig', [
            'urls' => $urls,
        ]);
    }
}