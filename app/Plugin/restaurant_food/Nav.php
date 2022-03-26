<?php

namespace Plugin\restaurant_food;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
//            'restaurant' => [
//                'name' => 'レストラン',
//                'icon' => 'fa fa-tag',
//                'children' => [
//                    'restaurantlist' => [
//                        'name' => 'レストラン一覧',
//                        'url'  => 'restaurant_admin'
//                    ],
//                    'restaurant_new' => [
//                        'name' => 'レストラン登録',
//                        'url'  => 'restaurant_new'
//                    ]
//                ]
//            ]
        ];
    }
}
