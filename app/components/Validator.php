<?php

namespace app\components;

use Valitron\Validator as Valitron;
use app\Component;
use app\App;
/**
 * Class Help
 * @package app\components
 */
class Validator extends Component
{
    protected $validator;

    protected $csrfErrorText = 'You session is expired. Try again';
    /**
     * @param $data
     * @param $rules
     * @return array|bool
     */
    public function validate($data, $rules)
    {
        $this->validator = new Valitron($data);
        $this->validator->rules($rules);

        if ($this->checkCsrf($data, $rules) && $this->validator->validate()) {
            return true;
        } else {
            // Errors
            $errors = $this->validator->errors();
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

    /**
     * @param $data
     * @param $rules
     */
    protected function checkCsrf($data, $rules)
    {
        $check = false;
        $out = true;
        foreach ($rules['required'] as $required) {
            if (in_array('csrf', $required)) {
                $check = true;
                break;
            }
        }
        if ($check) {
            $session = App::getComponent('session');
            if (!isset($data['csrf']) || !$session->checkCsrf($data['csrf'])) {
                $out = false;
                $this->validator->error('csrf', $this->csrfErrorText);
            }
        }
        return $out;
    }

}