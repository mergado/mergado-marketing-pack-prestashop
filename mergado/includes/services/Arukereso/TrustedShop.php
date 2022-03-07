<?php

/** Trusted Shop engine class, which provides purchase data sending. 
 * Requires PHP version 5.3.0 or higher.
 * Requires PHP curl package (php5-curl, libcurl, curl).
 * @version 2.0
 * @author Árukereső.hu 2016 */
class TrustedShop {

  const VERSION = '2.0/PHP';

  const SERVICE_URL_SEND = 'https://www.arukereso.hu/';
  const SERVICE_URL_AKU = 'https://assets.arukereso.com/aku.min.js';
  const SERVICE_TOKEN_REQUEST = 't2/TokenRequest.php';
  const SERVICE_TOKEN_PROCESS = 't2/TrustedShop.php';

  const ERROR_EMPTY_EMAIL = "Customer e-mail address is empty.";
  const ERROR_EMPTY_WEBAPIKEY = "Partner WebApiKey is empty.";
  const ERROR_EXAMPLE_EMAIL = "Customer e-mail address has been not changed yet.";
  const ERROR_EXAMPLE_PRODUCT = "Product name has been not changed yet.";
  const ERROR_TOKEN_REQUEST_TIMED_OUT = "Token request timed out.";
  const ERROR_TOKEN_REQUEST_FAILED = "Token request failed.";
  const ERROR_TOKEN_BAD_REQUEST = "Bad request: ";

  protected $WebApiKey;
  protected $Email;
  protected $Products = [];

  /** Instantiates a new Trusted Shop engine with the specified WebApi key.
   * @param string $WebApiKey - Your unique WebApi key. */
  public function __construct($WebApiKey) {
    $this->WebApiKey = $WebApiKey;
  }

  /** Sets the customer's e-mail address.
   * @param string $Email - Current customer's e-mail address. */
  public function SetEmail($Email) {
    $this->Email = $Email;
  }

  /** Adds a product to send. Callable multiple times.
   * @param string $ProductName - A product name from the customer's cart.
   * @param string $ProductId - A product id, it must be same as in the feed. */
  public function AddProduct($ProductName, $ProductId = null) {
    $Content = [];
    $Content['Name'] = $ProductName;
    if(!empty($ProductId)) {
      $Content['Id'] = $ProductId;
    }
    $this->Products[] = $Content;
  }

  /** Prepares the Trusted code, which provides data sending from the customer's browser to us.
   * @return string - Prepared Trusted code (HTML). */
  public function Prepare() {
    if (empty($this->WebApiKey)) {
      throw new Exception(self::ERROR_EMPTY_WEBAPIKEY);
    }
    if (empty($this->Email)) {
      throw new Exception(self::ERROR_EMPTY_EMAIL);
    }
    if ($this->Email == 'somebody@example.com') {
      throw new Exception(self::ERROR_EXAMPLE_EMAIL);
    }
    $Examples = ['Name of first purchased product', 'Name of second purchased product'];
    foreach($Examples as $Example) {
      foreach($this->Products as $Product){
        if($Product['Name'] == $Example) {
          throw new Exception(self::ERROR_EXAMPLE_PRODUCT);
        }
      }
    }
    
    $Params = [];
    $Params['Version'] = self::VERSION;
    $Params['WebApiKey'] = $this->WebApiKey;
    $Params['Email'] = $this->Email;
    $Params['Products'] = json_encode($this->Products);

    $Random = md5($this->WebApiKey . microtime());
    $Query =  $this->GetQuery($Params);


    // Sending:
    $Output = '<script type="text/javascript">window.aku_request_done = function(w, c) {';
    $Output.= 'var I = new Image(); I.src="' . self::SERVICE_URL_SEND . self::SERVICE_TOKEN_PROCESS . $Query . '" + c;';
    $Output.= '};</script>';
    // Include:
    $Output.= '<script type="text/javascript"> (function() {';
    $Output.= 'var a=document.createElement("script"); a.type="text/javascript"; a.src="' . self::SERVICE_URL_AKU . '"; a.async=true;';
    $Output.= '(document.getElementsByTagName("head")[0]||document.getElementsByTagName("body")[0]).appendChild(a);';
    $Output.= '})();</script>';
    // Fallback:
    $Output.= '<noscript>';
    $Output.= '<img src="' . self::SERVICE_URL_SEND . self::SERVICE_TOKEN_PROCESS . $Query . $Random . '" />';
    $Output.= '</noscript>';

    return $Output;
  }

  /** Performs a request on our servers to get a token and assembles query params with it.
   * @param array $Params - Parameters to send with token request.
   * @return string - Query string to assemble sending code snipet on client's side with it. */
  protected function GetQuery($Params) {
    // Prepare curl request:
    $Curl = curl_init(); 
    curl_setopt($Curl, CURLOPT_URL, self::SERVICE_URL_SEND . self::SERVICE_TOKEN_REQUEST);
    curl_setopt($Curl, CURLOPT_POST, 1);
    curl_setopt($Curl, CURLOPT_POSTFIELDS, http_build_query($Params));
    curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT_MS, 500);
    curl_setopt($Curl, CURLOPT_TIMEOUT_MS, 500);
    curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($Curl, CURLOPT_HEADER, true);


    // Execute the request:
    $Response = curl_exec($Curl);

    if(curl_errno($Curl) === 0 && $Response !== false) {
      $Info = curl_getinfo($Curl);
      $StatusCode = $Info['http_code'];

      $JsonBody = substr($Response, $Info['header_size']);
      $JsonArray = json_decode($JsonBody, true);
      $JsonError = json_last_error();

      curl_close($Curl);

      if(empty($JsonError)) {
        if ($StatusCode == 200){
          $Query = [];
          $Query[]= 'Token=' . $JsonArray['Token'];
          $Query[]= 'WebApiKey=' . $this->WebApiKey;
          $Query[]= 'C=';
          return '?' . join('&', $Query);
        } else if ($StatusCode == 400){
          throw new Exception(self::ERROR_TOKEN_BAD_REQUEST . $JsonArray['ErrorCode'] . ' - ' . $JsonArray['ErrorMessage']);
        } else {
          throw new Exception(self::ERROR_TOKEN_REQUEST_FAILED);
        }
      } else {
        throw new Exception('Json error: ' . $JsonError);
      }
    } else {
      throw new Exception(self::ERROR_TOKEN_REQUEST_TIMED_OUT);
    }

    return null;
  }
}