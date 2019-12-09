<?php

namespace App\Controllers;

use App\Models\SaleHeader;

use Carbon\Carbon;
 
class SalesReportsController extends Controller
{
	public function __invoke($request, $response, $params)
	{
		$company = $_SESSION["company_session"];
		$company->load('customers');
		
		$project = $_SESSION["project_session"];
		$project->load('salesDocumentsTypes');
		
		$args = [
			"navbar" => [
				"username_session" 		=> $_SESSION["user_session"]->username,
				"display_name_session" 	=> $_SESSION["user_session"]->display_name,
				"project_session" 		=> $project->full_name,
				"company_session" 		=> $company->business_name,
			],
			"customers" 			=> $company->customers->sortBy("business_name"),
			"salesDocsTypes" 		=> $project->salesDocumentsTypes->sortBy("description"),
		];		
		
		return $this->container->renderer->render($response, 'sales_report.phtml', $args);
	}
	
	public function report($request, $response, $args)
	{
		$customers_ids 			= (isset($request->getParsedBody()["customers_ids"]) ? $request->getParsedBody()["customers_ids"] : null);
		$sales_docs_codes		= (isset($request->getParsedBody()["sales_docs_codes"]) ? $request->getParsedBody()["sales_docs_codes"] : null);
		
		$sales = SaleHeader::where('project_id', $_SESSION['project_session']->id)
										->where('is_canceled', false)
										->when($customers_ids != null, function($query) use ($customers_ids) {
											$query->whereIn('customer_id', $customers_ids);
										})
										->when($sales_docs_codes != null, function($query) use ($sales_docs_codes) {
											$query->whereIn('document_type_code', $sales_docs_codes);
										})
	                                    ->orderBy('dated_at', 'ASC')
	                                    ->get();
	                                    
	    $records = Array();
	    
	    $sal = 0;
		
		$find 		= ['Ñ', 'ñ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'];
		$replace 	= ['N', 'n', 'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'];
		
	    while (count($sales) > $sal)
	    {
			// take older first...
			$document = $sales[$sal];
			$sal++;
			
	        array_push($records, (object)[
                "id"            => $document->id,
                "dated_at"      => $document->dated_at,
                "unique_code"   => $document->document_type_code,
                "business_name" => $document->customer->business_name,
				"comments"		=> $document->comments,
            ]);
	    }
	    				
		$responseHTML =	$this->padr("ID", 6, " ") .
						$this->padr("FECHA", 12, " ") .
						$this->padr("TIPO", 6, " ") .
						$this->padr("CLIENTE", 20, " ") .
						$this->padr("COMENTARIOS", 15, " ") . "\n" .
						str_repeat("-", 137) . "\n";
		
		foreach($records as $record)
	    {
	        $responseHTML .=	$this->padr($record->id, 6, " ") .
	                            $this->padr($record->dated_at, 12, " ") .
	                            $this->padr($record->unique_code, 6, " ") .
	                            $this->padr($record->business_name, 20, " ", " ") .
	                            $this->padr($record->comments, 15, " ", " ") . "\n";
	                            
	    }
		
		return $response->withJson([
			"Result" 	=> "OK",
			"Records" 	=> html_entity_decode($responseHTML, ENT_QUOTES, "UTF-8"),
		]);
	}
	
	private function padr($str, $len, $char, $lastChar = "")
	{
	    $str = trim($str);
	    if (strlen($str) > $len)
	        $result = substr($str, 0, $len);
		else
			$result = str_pad($str, $len, $char, STR_PAD_RIGHT);
		
		// last char
		if ($lastChar != "")
			$result[strlen($result)-1] = $lastChar;
		
		return $result;
	}
	
	private function padl($str, $len, $char)
	{
	    $str = trim($str);
	    if (strlen($str) > $len)
	        return substr($str, 0, $len);
	    return str_pad($str, $len, $char, STR_PAD_LEFT);
	}
	
	private function parsedFloat($num, $dec = 2)
	{
		if ($num != 0) {
			$rounded	= round($num * pow(10, $dec), 0);
			$strnum   	= strval($rounded);
			$intval     = substr($strnum, 0, strlen($strnum) - $dec);
			$decval     = substr($strnum, strlen($strnum) - $dec);
			
			return $intval . "." . $decval;
		}
		
		return "0." . str_repeat("0", $dec);
	}
}