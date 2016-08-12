<?php

namespace Con4gis\ApiBundle\Controller;

use Contao\CoreBundle\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use c4g\Core\C4GApiCache;


class ApiController extends FrontendController
{

    /**
     * @var string
     */
    private $_sApiUrl = 'system/modules/con4gis_core/api/index.php';

    public function runAction($_url_fragment)
    {

        // Get path
        $arrFragments = $this->getFramgentsFromRoutingParam($_url_fragment);

        // Extract api endpoint
        $strApiEndpoint = array_shift($arrFragments);

        $blnUseCache = false;
        $blnOutputFromCache = false;

        if (!\Config::get('debugMode') && (\Config::get('cacheMode') == 'both' || \Config::get('cacheMode') == 'server') && !in_array($strApiEndpoint, $GLOBALS['CON4GIS']['PREVENT_CACHE']['SERVICES']))
        {
            $blnUseCache = true;
        }

        if (is_array($GLOBALS['CON4GIS']['PREVENT_CACHE']['PARAMS']))
        {
            foreach ($GLOBALS['CON4GIS']['PREVENT_CACHE']['PARAMS'] as $key=>$arrValues)
            {
                if (\Input::get($key) && in_array(\Input::get($key), $arrValues))
                {
                    $blnUseCache = false;
                }
            }
        }


        if ($blnUseCache)
        {
            // check for cached data
            if ($strResponse = C4GApiCache::getCacheData($strApiEndpoint, $arrFragments))
            {
                $blnOutputFromCache = true;
            }
        }

        if (!$blnOutputFromCache)
        {

            // Create the api endpoint handler
            $objHandler = new $GLOBALS['TL_API'][$strApiEndpoint]();

            $strResponse = $objHandler->generate($arrFragments);

            if ($blnUseCache)
            {
                // write data into cache
                C4GApiCache::putCacheData($strApiEndpoint, $arrFragments, $strResponse);
            }

        }


        $response = new JsonResponse(json_decode($strResponse));

        if (\Input::get('callback'))
        {
            $response->setCallback(\Input::get('callback'));
        }

        return $response;

    }

    protected function getFramgentsFromRoutingParam($strUrlFrament)
    {
        // return the fragments
        return explode('/', $strUrlFrament);
    }


    /**
     * Split the request into fragments and find the api resource
     */
    protected function getFragmentsFromUrl($request)
    {

        // Return null on empty request path
        if ($request == '') {
            return null;
        }

        echo \Environment::get('request');

        // Get the request string without the index.php fragment
        if (\Environment::get('request') == $this->_sApiUrl . 'index.php') {
            $strRequest = '';
        } else {
            list($strRequest) = explode('?', str_replace($this->_sApiUrl . 'index.php/', '', \Environment::get('request')), 2);
        }

        // Remove api fragment
        if (substr($strRequest, 0, strlen($this->_sApiUrl)) == $this->_sApiUrl) {
            $strRequest = substr($strRequest, strlen($this->_sApiUrl));
        }

        // URL decode here
        $strRequest = rawurldecode($strRequest);
        $strRequest = substr($strRequest,1);

        // return the fragments
        return explode('/', $strRequest);
    }

}