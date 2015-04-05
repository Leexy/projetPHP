<?php
// Access Control List
use Zend\Permissions\Acl\Acl as ZendAcl;

class Acl extends ZendAcl
{
  public function __construct()
  {
    // APPLICATION ROLES
    $this->addRole('guest');
    $this->addRole('member', 'guest');
    $this->addRole('admin');

    // APPLICATION RESOURCES
    $this->addResource('/');
    $this->addResource('/games');
    $this->addResource('/games/:id');
    $this->addResource('/games/:id/state');
    $this->addResource('/signup');
    $this->addResource('/login');
    $this->addResource('/logout');

    // APPLICATION PERMISSIONS
    $this->allow('guest', '/', 'GET');
    $this->allow('guest', '/signup', ['GET', 'POST']);
    $this->allow('guest', '/login', ['GET', 'POST']);
    $this->allow('guest', '/logout', 'GET');

    $this->allow('member', '/games', ['GET', 'POST']);
    $this->allow('member', '/games/:id', 'GET');
    $this->allow('member', '/games/:id/state', 'GET');

    $this->allow('admin');
  }
}
