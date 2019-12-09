<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function($request, $response) {
	return $response->withRedirect($this->router->pathFor('suppliers'));
});

$app->group('/login', function() use ($sessionAuth, $companyAuth) {
	$this->get('', 'LoginController')->setName('login');
	$this->post('', 'LoginController:login');
	$this->post('/logout', 'LoginController:logout');
	
	// password recovery
	$this->get('/recovery', 'LoginController:passwordRecovery')->setName('login');
	$this->post('/recovery', 'LoginController:sendTemporaryPassword');
	
	// companies options
	$this->group('/companies', function() {
		$this->get('/selection', 'LoginController:companiesSelection')->setName('companies_selection');
		$this->post('/selected/{company_id}', 'LoginController:companySelected');
	})->add($sessionAuth);
	
	// projects options
	$this->group('/projects', function() {
		$this->get('/selection', 'LoginController:projectsSelection')->setName('projects_selection');
		$this->post('/selected/{project_id}', 'LoginController:projectSelected');
	})->add($companyAuth)->add($sessionAuth);
})->add($hostAuth);

// customers
$app->group('/customers', function() {
	$this->get('', 'CustomersController')->setName('customers');
	$this->post('/{action}[/{customer_id}]', 'CustomersController:action');
})->add($appAuth)->add($sessionAuth)->add($hostAuth);