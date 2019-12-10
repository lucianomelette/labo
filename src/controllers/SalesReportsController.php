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
		$body = $request->getParsedBody();

		$customers_ids 			= (isset($body["customers_ids"]) ? $body["customers_ids"] : null);
		$sales_docs_codes		= (isset($body["sales_docs_codes"]) ? $body["sales_docs_codes"] : null);
		$dated_at				= (isset($body["dated_at"]) ? $body["dated_at"] : null);
		
		$sales = SaleHeader::where('project_id', $_SESSION['project_session']->id)
										->where('is_canceled', false)
										->when($customers_ids != null, function($query) use ($customers_ids) {
											$query->whereIn('customer_id', $customers_ids);
										})
										->when($sales_docs_codes != null, function($query) use ($sales_docs_codes) {
											$query->whereIn('document_type_code', $sales_docs_codes);
										})
										->when($dated_at != null, function($query) use ($dated_at) {
											$query->where('dated_at', $dated_at);
										})
	                                    ->orderBy('dated_at', 'ASC')
										->get();
										
	    $records = Array();
	    
	    $sal = 0;
		
		$find 		= ['Ñ', 'ñ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'];
		$replace 	= ['N', 'n', 'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'];
		
	    foreach ($sales as $document)
	    {	
			$datedAt = Carbon::createFromFormat('Y-m-d h:i:s', $document->dated_at);

	        array_push($records, (object)[
                "id"            => $document->id,
                "dated_at"      => $datedAt->format('d/m/Y'),
                "timed_at"      => $datedAt->format('h:i'),
                "business_name" => $document->customer->business_name,
                "email" 		=> $document->customer->email,
                "location" 		=> $document->customer->location,
                "contact_name" 	=> $document->customer->contact_name,
                "phone_number" 	=> $document->customer->phone_number,
				"comments"		=> $document->comments,
            ]);
	    }
		
		/*
		$responseHTML =	$this->padr("ID", 6, " ") .
						$this->padr("FECHA", 12, " ") .
						$this->padr("HORA", 7, " ") .
						$this->padr("CLIENTE", 20, " ") .
						$this->padr("EMAIL", 20, " ") .
						$this->padr("DOMICILIO", 20, " ") .
						$this->padr("CONTACTO", 20, " ") .
						$this->padr("TELEFONO", 20, " ") .
						$this->padr("COMENTARIOS", 15, " ") . "\n" .
						str_repeat("-", 137) . "\n";
		
		foreach($records as $record)
	    {
	        $responseHTML .=	$this->padr($record->id, 6, " ") .
	                            $this->padr($record->dated_at, 12, " ") .
	                            $this->padr($record->timed_at, 7, " ") .
	                            $this->padr($record->business_name, 20, " ", " ") .
	                            $this->padr($record->email, 20, " ", " ") .
	                            $this->padr($record->location, 20, " ", " ") .
	                            $this->padr($record->contact_name, 20, " ", " ") .
	                            $this->padr($record->phone_number, 20, " ", " ") .
	                            $this->padr($record->comments, 15, " ", " ") . "\n";
	                            
		}
		*/
		
		return $response->withJson([
			"Result" 	=> "OK",
			"Records" 	=> $records,
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