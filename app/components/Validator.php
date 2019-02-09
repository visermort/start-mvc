<?php

namespace app\components;

use Valitron\Validator as Valitron;
use app\Component;
/**
 * Class Help
 * @package app\components
 */
class Validator extends Component
{

    /**
     * @param $data
     * @param $rules
     * @return array|bool
     */
    public function validate($data, $rules)
    {
        $validator = new Valitron($data);
        $validator->rules($rules);

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