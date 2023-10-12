<?php
    require_once('defines.php');
    #help to call endpoints for the API
    Class instagram_basic_display_api{
        private $_appId= INSTAGRAM_APP_ID;
        private $_appSecret= INSTAGRAM_APP_SECRET;
        private $_redirectUri= INSTAGRAM_APP_REDIRECT_URL;
        private $_getCode='';
        private $_apiBaseUrl= 'https://api.instagram.com/';
        private $_graphBaseUrl= 'https://graph.instagram.com/';
        private $_userAccessToken='';
        private $_userAccessTokenExpiresAt='';
        public $_authorizationUrl='';
        public $_hasUserAccessToken=false;
    

        function __construct( $params ){
            //save instagram code
            $this->_getCode= $params['get_code'];

            //get auth url
            $this->_setAuthorizationUrl();

            //get access token
            $this->_setUserInstagramAccessToken( $params);


        }
        public function getUserAccessToken() {
			return $this->_userAccessToken;
		}
        public function getUserAccessTokenExpires() {
			return $this->_userAccessTokenExpiresAt;
		}
        private function _setAuthorizationUrl(){
            $getVars = array(
                'app_id'=> $this->_appId,
                'redirect_uri'=> $this->_redirectUri,
                'scope'=> 'user_profile,user_media',
                'response_type'=> 'code'
                );

                //create url
                $this->_authorizationUrl= $this->_apiBaseUrl.'oauth/authorize/?'.http_build_query($getVars);
            }

        private function _setUserInstagramAccessToken( $params ){
            if ($params['get_code']){ //Checks the access token
               $userAccessTokenResponse= $this->_getUserAccessToken();
               $this->_userAccessToken= $userAccessTokenResponse['access_token'];
               $this->_hasUserAccessToken= true;

               //get long lived access token
               $longLivedAccessTokenResponse= $this->_getLongLivedAccessToken();
               $this->_userAccessToken= $longLivedAccessTokenResponse['access_token'];
               $this->_userAccessTokenExpiresAt = $longLivedAccessTokenResponse['expires_in'];

            }
        }
        private function _getUserAccessToken(){
            $params = array(
                'endpoint_url'=>$this->_apiBaseUrl.'oauth/access_token',
                'type'=>'POST',
                'url_params'=>array(
                    'app_id'=> $this->_appId,
                    'app_secret'=> $this->_appSecret,
                    'grant_type'=> 'authorization_code',
                    'redirect_uri'=> $this->_redirectUri,
                    'code'=> $this->_getCode
                    )
                );
                $response= $this->_callApi($params);
                return $response;
    }
            private function _getLongLivedAccessToken(){
                    $params = array(
                        'endpoint_url'=>$this->_graphBaseUrl.'access_token',
                        'type'=>'GET',
                        'url_params'=>array(
                            'client_secret'=> $this->_appSecret,
                            'grant_type'=> 'ig_exchange_token',
                            
                            )
                        );
                        $response= $this->_callApi($params);
                        return $response;
        
    }
    private function _callApi( $params ){
        $curl=curl_init();
        $endpoint=$params['endpoint_url'];
        if( 'POST' == $params['type'] ){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params['url_params']));
            curl_setopt($curl, CURLOPT_POST, 1);
        }elseif('GET'==$params['type']){
            $params['url_params']['access_token']= $this->_userAccessToken;

            //add params to endpoint
            $endpoint.= '?'.http_build_query($params['url_params']);
        }
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $response= curl_exec($curl);
        curl_close($curl);

        $responseArray= json_decode($response,true);

        if ( isset( $responseArray['error_type'] ) ) {
            var_dump( $responseArray );
            die();
        } else {
            return $responseArray;
        }

    }
    }