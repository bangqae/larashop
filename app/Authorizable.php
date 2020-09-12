<?php

namespace App;

use Illuminate\Support\Arr; // use Arr;

trait Authorizable
{
    private $abilities = [
        'index' => 'view',
        'edit' => 'edit',
        'show' => 'view',
        'update' => 'edit',
        'create' => 'add',
        'store' => 'add',
        'destroy' => 'delete',

        'options' => 'view',
        'store_option' => 'add',
        'edit_option' => 'edit',
        'update_option' => 'edit',
        'remove_option' => 'delete'
    ];

    /**
     * Override of callAction to perform the authorization before
     *
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        if( $ability = $this->getAbility($method) ) {
            $this->authorize($ability);
        }

        return parent::callAction($method, $parameters);
    }

    public function getAbility($method)
    {
        $routeName = explode('.', \Request::route()->getName()); // Example : products.index to ( [0] => products [1] => index )
        $action = Arr::get($this->getAbilities(), $method); // Change method name to abilities, example : index => view

        return $action ? $action . '_' . $routeName[0] : null; // Take index 0 from explode(), example return : view_products
    }

    private function getAbilities()
    {
        return $this->abilities;
    }

    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
    }
}
