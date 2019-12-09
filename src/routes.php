<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function($request, $response) {
	return $response->withRedirect($this->router->pathFor('customers'));
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

// ************* //
// **  SALES  ** //
// ************* //

// customers
$app->group('/customers', function() {
	$this->get('', 'CustomersController')->setName('customers');
	$this->post('/{action}[/{customer_id}]', 'CustomersController:action');
})->add($appAuth)->add($sessionAuth)->add($hostAuth);

// sales
$app->group('/sales', function() {
	// purchases reports
	$this->get('/report', 'SalesReportsController');
	$this->post('/report', 'SalesReportsController:report');
	
	// query
	$this->get('/query', 'SalesController:query');
	
	// general
	$this->get('[/{headerId}]', 'SalesController');
	$this->post('/{action}[/{headerId}]', 'SalesController:action');
})->add($appAuth)->add($sessionAuth)->add($hostAuth);

// ************* //
// **  STOCK  ** //
// ************* //

// products
$app->group('/products', function() {
	$this->get('', 'ProductsController');
	$this->post('/{action}', 'ProductsController:action');
})->add($appAuth)->add($sessionAuth)->add($hostAuth);