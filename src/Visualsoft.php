<?php
namespace Mikkelson\VisualSoft;

class VisualsoftException extends \Exception {}

class Visualsoft{
    
    protected $username;
    protected $password;
    protected $clientId;
    
    /*
     * General Properties
     */
    
    protected $SoapClient;
    protected $namespace = '/api/soap/service';
    protected $wsdl = '/api/soap/wsdl/2';
    protected $errors;
    
    public function __construct(){}
    
    /*
     * Do soap request
     */
    
    protected function request($method = 'HelloWorld', $params = array()){
        
        if(empty($this->clientId)){
            $this->errors ='Set your API details with Visualsoft:setClient() before making requests';
            return;
        }
        
        //attatch authentication headers
        $this->addHeaders();
        if(!empty($this->errors)){
            return;
        }
        
        try{

            $response = $this->SoapClient->$method($params);

        }catch (\Exception $e){
            $this->errors = str_replace(array('SOAP-ERROR:','Encoding:','  '), '', $e->getMessage());
            return;
        }
        
        if(!empty($response->Errors->Error)){
            $this->errors = $response->Errors->Error;
            return;
        }

        return $response->Result;    
    }
    
    /*
     * Download an order by order_id
     * http://demo.visualsoft.co.uk/api/soap#GetOrder
     */
    
    public function getOrderById($order_id = null){
       
        $param = new \SoapVar($order_id, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", 'order_id');  
        return $this->getOrder($param);
       
    }
    
    /*
     * Wrapper to download an order by order_ref
     * http://demo.visualsoft.co.uk/api/soap#GetOrder
     */
    
    public function getOrderByRef($order_ref = null){
       
        $param = new \SoapVar($order_ref, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", 'order_ref');  
        return $this->getOrder($param);
       
    }
    
    /*
     * Download order
     */
    
    protected function getOrder($param){
        
        $data = null;
        $result = $this->request('GetOrder', $param);
        
        if(!empty($result->WEB_ORDERS->WEB_ORDER)){
            $data = $result->WEB_ORDERS->WEB_ORDER;
        }
        
        return array('data' => $data, 'errors' => $this->errors);
    }
    
    /*
     * Download orders by date range
     * http://demo.visualsoft.co.uk/api/soap#GetOrdersByDateRange
     */
    
    public function getOrdersByDate($date){
       
        $data =  null;
        $param = new \SoapVar($date, XSD_STRING, "date", "http://www.w3.org/2001/XMLSchema");         
        $result = $this->request('GetOrdersByDateRange', $param);

        //the point in the below logic is to keep result indexes consistent
        if(!empty($this->errors) || empty($result->WEB_ORDERS->WEB_ORDER)){
            //unset empty WEB_ORDERS object to keep returned data structure consistent
            unset($result->WEB_ORDERS);
        }elseif(count($result->WEB_ORDERS->WEB_ORDER) > 1){
            $data = $result->WEB_ORDERS->WEB_ORDER;
        }else{
            $data = array($result->WEB_ORDERS->WEB_ORDER);
        }
        
        return array('data' => $data, 'errors' => $this->errors);
    }
    
    /*
     * Builds HelloWorld Request
     * http://demo.visualsoft.co.uk/api/soap#HelloWorld
     */
    
    public function helloWorld(){

        $data = null;
        $param = new \SoapVar("", XSD_ANYXML);
        $result = $this->request('HelloWorld', $param);

        if(empty($this->errors) && !empty($result->Success)){
             $data = $result->Success;
        }
        
        return array('data' => $data, 'errors' => $this->errors);
    }
    
    /*
     * Updates the status of an existing order
     */
    
    public function updateOrderStatus($order_id, $status, $tracking = null, $comments = null, $label_url = null){

        $data = null;
        $param = new \SoapVar('<order_update_data xsi:type="xsd:string"><![CDATA[<ORDER_STATUSES><ORDER_STATUS><ORDER_ID>'.$order_id.'</ORDER_ID><ORDER_STATUS>'.$status.'</ORDER_STATUS><SEND_CUSTOMER_EMAIL>true</SEND_CUSTOMER_EMAIL><BASIC_TRACKING_NUMBER>'.$tracking.'</BASIC_TRACKING_NUMBER><CUSTOMER_COMMENTS>'.$comments.'</CUSTOMER_COMMENTS><SHIPPING_LABEL>'.$label_url.'</SHIPPING_LABEL></ORDER_STATUS></ORDER_STATUSES>]]></order_update_data><data_type xsi:type="xsd:string">xml</data_type>', XSD_ANYXML);
        $result = $this->request('OrderStatusUpdate', $param);
        
        if(empty($this->errors)){
             $data = $result;
        }
        
        return array('data' => $data, 'errors' => $this->errors);
        
    }
    
    /*
     * Builds soap headers
     */
    
    protected function addHeaders(){
        
        //check wsdl is available
        $wsdl = @file_get_contents($this->wsdl);
        if(!$wsdl){
            $this->errors = 'Unable to connect to the Visualsoft API on '.$this->wsdl;
            return;
        }
  
        $this->SoapClient = new \SoapClient($this->wsdl,  array(
            "trace" => true,  
            "exceptions" => true,
            'use' => SOAP_LITERAL
        ));    
           
        $headerbody = (object) array(
            'ClientId' => $this->clientId, 
            'Username' => $this->username, 
            'Password' => $this->password
        );
        
        $header = new \SOAPHeader($this->namespace, 'VSAuth', $headerbody);
        $this->SoapClient->__setSoapHeaders($header);
        
    }
    
    /*
     * Setup Visualsoft client details
     */
    
    public function setClient($data = array()){
        
        if(empty($data['client_id']) || empty($data['username']) || empty($data['password']) || empty($data['domain'])){
            return array('errors' => "You must privide an array, with keys client_id, username, password and domain");
        }
        
        $this->clientId = $data['client_id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        
        $this->namespace = 'http://'.$data['domain'].$this->namespace;
        $this->wsdl = 'http://'.$data['domain'].$this->wsdl;
        
    }
    
    /*
     * Returns last response and request XML for debugging
     */
    
    public function getRequestResponse(){
        
        if(empty($this->SoapClient)){
            return;
        }
        
        $data['request']  = $this->SoapClient->__getLastRequest();
        $data['response'] = $this->SoapClient->__getLastResponse();
        return $data;
    }
}