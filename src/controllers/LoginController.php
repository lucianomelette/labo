<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Project;
 
class LoginController extends Controller
{
	public function __invoke($request, $response)
	{	
		return $this->container->renderer->render($response, 'login.phtml');
	}
	
	public function login($request, $response, $args)
	{
		$body = $request->getParsedBody();

		$username = $body['username'];
		$password = $body['password'];
		
		$user = User::where('username', $username)
					->where('password', $password)
					->first();
		
		if ($user != null) {
			$_SESSION['user_session'] = $user;
			
			return $response->withJson([ 'status' => 'ok' ]);
		}
		
		return $response->withJson([ 'status' => 'error' ]);
	}
	
	public function logout($request, $response, $args)
	{
		unset($_SESSION['user_session']);
		unset($_SESSION['company_session']);
		unset($_SESSION['project_session']);
		
		return $response->withJson([
			'status' => 'ok',
		]);
	}
	
	// password recovery
	public function passwordRecovery($request, $response)
	{	
		return $this->container->renderer->render($response, 'login_password_recovery.phtml');
	}
	
	public function sendTemporaryPassword($request, $response, $args)
	{	
		return $response->withJson([
			'status' => 'ok'
		]);
	}
	
	// companies options
	public function companiesSelection($request, $response)
	{	
		unset($_SESSION['company_session']);
		unset($_SESSION['project_session']);
	
		$companies = User::find($_SESSION["user_session"]->id)
					->companies;
	
		if (count($companies) == 1)
		{
			$this->setCompanySession($companies[0]->id);
			return $response->withRedirect($this->container->router->pathFor('projects_selection'));
		}

		$args = [
			"navbar" => [
				"username_session" 		=> $_SESSION["user_session"]->username,
				"display_name_session" 	=> $_SESSION["user_session"]->display_name,
			],
			"companies" => $companies,
		];
		
		return $this->container->renderer->render($response, 'login_companies_selection.phtml', $args);
	}
	
	public function companySelected($request, $response, $args)
	{	
		$this->setCompanySession($args["company_id"]);
		
		return $response->withJson([
			'status' => 'ok',
		]);
	}

	private function setCompanySession($companyId)
	{
		$_SESSION["company_session"] = Company::find($companyId);
	}
	
	// projects options
	public function projectsSelection($request, $response)
	{	
		unset($_SESSION['project_session']);
	
		$projects = User::find($_SESSION["user_session"]->id)
					->projects
					->where('company_id', $_SESSION["company_session"]->id);
	
		if (count($projects) == 1)
		{
			$this->setProjectSession($projects[0]->id);
			return $response->withRedirect($this->container->router->pathFor('sales_creation'));
		}

		$args = [
			"navbar" => [
				"username_session" 		=> $_SESSION["user_session"]->username,
				"display_name_session" 	=> $_SESSION["user_session"]->display_name,
				"company_session" 		=> $_SESSION["company_session"]->business_name,
			],
			"projects" => $projects,
		];
		
		return $this->container->renderer->render($response, 'login_projects_selection.phtml', $args);
	}
	
	public function projectSelected($request, $response, $args)
	{	
		$this->setProjectSession($args["project_id"]);
		
		return $response->withJson([
			'status' => 'ok',
		]);
	}

	private function setProjectSession($projectId)
	{
		$_SESSION["project_session"] = Project::find($projectId);
	}
}