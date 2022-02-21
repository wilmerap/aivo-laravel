<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;

class AivoController extends Controller
{
    //
    public function __construct()
    {


        header('Access-Control-Allow-Origin: *');

        $this->client = new Client();
    
        /** Inicio - Obtener Token de Acceso de la APP Spotify **/
        // Header y Form Data
        $headers = [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ZDMzNTMwZDdhNWJhNGI5NWExZThhZGFiYzBlNmI3NjQ6MjFlNWFlYWY3Njk1NDc0NWFhM2FiMzM3MjY4YTgzOWQ='
                    ];
        $body = 'grant_type=client_credentials';
        
        // Send an asynchronous request.
        $request = new \GuzzleHttp\Psr7\Request('POST', 'https://accounts.spotify.com/api/token', $headers, $body);
        $promise = $this->client->sendAsync($request)->then(function ($response) {
    
            $access_token = json_decode($response->getBody(), true);
            // Token para consultas Web
            $this->token_app = $access_token['access_token'];
            
        });
        
        $promise->wait();
        
    }

    public function getBuscadorNombreBanda($name_artista)
    {

    /** Inicio - Buscador por Nombre de la Banda o Artista*/
    
        // Header
        $headers = [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$this->token_app
                    ];
        
        // Send an asynchronous request.
        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.spotify.com/v1/search?q='.urlencode($name_artista).'&type=artist', $headers);
        $promise = $this->client->sendAsync($request)->then(function ($response) {
    
            $items_artist = json_decode($response->getBody(), true);
            /** ID del Artista */
            $this->id_artist = $items_artist['artists']['items'][0]['id'];
            
        });
        
        $promise->wait();

        // Resultado de consulta de nombre artista
        return $this->id_artist;

    }
    
    public function getConsultaAlbum(Request $rec)
    {

    /** Recibo parametros de artista solicitado */
    $id_artist = $this->getBuscadorNombreBanda($rec->artista);

    /** Inicio - Consulta de Albunes del Artista APP Spotify */
    
        // Header
        $headers = [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$this->token_app
                    ];
        
        // Send an asynchronous request.
        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.spotify.com/v1/artists/'.$id_artist.'/albums', $headers);
        $promise = $this->client->sendAsync($request)->then(function ($response) {
    
            $this->items_artist = json_decode($response->getBody(), true);
            
        });
        
        $promise->wait();
        
        // Array de Listado de Albunes
        $album = array();
        $list_albums = array();
    
        foreach ($this->items_artist['items'] as $ia) {
            /**  Listado de Albunes  **/
            $album = [
                'name' => $ia['name'],
                'released' => $ia['release_date'],
                'tracks' => $ia['total_tracks'],
                'cover' => array('height' => $ia['images'][0]['height'], 'width' => $ia['images'][0]['width'], 'url' => $ia['images'][0]['url']),
                ];
              $list_albums[] = $album;
    
        }
        
        $rec->headers->set('Accept', 'application/json');
        
        // Muestro JSON con listado de album del artista solicitado
        echo json_encode($list_albums);
        
    }
    
}
