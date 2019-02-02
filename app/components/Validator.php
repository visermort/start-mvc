<?php

namespace app\components;

use Valitron\Validator as Valitron;
/**
 * Class Help
 * @package app\components
 */
class Validator
{

    /**
     * @param $data
     * @param $rules
     * @return array|bool
     */
    public function validate($data, $rules)
    {
        //foreach ($data as &$item) {

        //}

        $validator = new Valitron($data);
        foreach ($rules as $rule) {
            $validator->rule($rule[0], $rule[1]);
        }

        if ($validator->validate()) {
            return true;
        } else {
            // Errors
            $errors = $validator->errors();
            $out = [];
            foreach ($errors as $key => $error) {
                $out[$key] = implode(' ', $error);
            }
            return $out;
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function clean($data)
    {
        foreach ($data as &$item) {
            $item = strip_tags($item);
        }
        return $data;
    }

}