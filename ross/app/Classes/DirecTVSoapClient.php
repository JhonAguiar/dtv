<?php

    namespace App\Classes;

    use \SoapClient;
    
    class DirecTVSoapClient extends SoapClient
    {
           /**
         *
         *  @param  url string
         *  @param  options array
         *  @brief Constructor. Establece la url a consumir y los parametros de conecciÃ³n
         *  @return void
         *
         */
        public function __construct($wsdl, $options = null) {
                parent::__construct($wsdl, $options);
        }
   
        /**
         *
         *  @param  request string
         *  @param  location string
         *  @param  action string
         *  @param  version string
         *  @param  one_way boolean
         *  @param  options array
         *  @brief Redefine el mÃ©todo __doRequest de SoapClient, adaptando los namespaces a los utilizados en API_ING
         *  @return void
         *
         */
        // public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        //         $replacements = array(
        //                 'SOAP-ENV'      => 'soapenv',
        //                 'ns1'           => 'bro'
        //         );

        //         foreach ($replacements as $original => $changed) {
        //                 $request = preg_replace("/{$original}(:|=)/", "{$changed}$1", $request);
        //         }

        //         //error_log($request);

        //         return parent::__doRequest($request, $location, $action, $version, $one_way);
        // }
    }
   
?>