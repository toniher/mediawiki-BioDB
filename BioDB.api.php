<?php

class ApiBioDB extends ApiBase
{
    public function execute()
    {

        $params = $this->extractRequestParams();

        $param = null;
        $data = null;
        $output = [];
        $table = false;
        $cols = null;
        $format = null;
        $sep = null;
        $typesolve = false; // No typesolving at the beginning
        $APIallow = true; // Should API be allowed from

        if (array_key_exists("param", $params)) {
            $param = $params["param"];
        }

        if (array_key_exists("table", $params)) {
            $table = $params["table"];
        }

        if (array_key_exists("fileformat", $params)) {
            $format = $params["fileformat"];
        }

        if (array_key_exists("sep", $params)) {
            $sep = $params["sep"];
        }

        if (array_key_exists("typesolve", $params)) {
            $typesolve = $params["typesolve"];
        }

        if (array_key_exists("query", $params)) {
            // Query new function in BioDB
            $output = BioDB::returnBioDB($params["query"], $param);
            $APIallow = $this->checkPermissions($params["query"]);
        }

        $data = $output;

        if ($typesolve) {
            $data = $this->processTyping($data);
        }

        if ($table) {

            $cols = [];

            $subs = $params["query"] . ".";
            $tablerows = [];

            $first = 0;
            foreach ($data as $row) {
                $tablerow = [];

                foreach ($row as $key => $val) {
                    array_push($tablerow, $val);

                    $key = str_replace($subs, "", $key);

                    if ($first === 0) {
                        array_push($cols, $key);
                    }
                }

                array_push($tablerows, $tablerow);
                $first = $first + 1;
            }

            $data = $tablerows;

        }

        $paramq = [];

        if ($param) {
            $paramq = explode(",", $param);
        }

        if ($APIallow) { // We allow API by default

            if ($table && $format == 'csv') {

                // TODO: Fix this ugly solution
                if ($sep) {
                    $csvstr = implode($sep, $cols) . "\n";
                } else {
                    $csvstr = implode("\t", $cols) . "\n";
                }

                foreach ($data as $row) {

                    if ($sep) {
                        $csvstr = $csvstr . implode($sep, $row) . "\n";
                    } else {
                        $csvstr = $csvstr . implode("\t", $row) . "\n";
                    }

                }

                header("Content-Type: application/csv");
                header("Content-Disposition: attachment; filename=" . $params["query"] . ".csv");
                header("Pragma: no-cache");
                header("Expires: 0");
                echo $csvstr;
                exit;

            } else {

                $this->getResult()->addValue(null, $this->getModuleName(), [ 'status' => "OK", 'query' => $params["query"], 'param' => $paramq, 'cols' => $cols, 'rows' => $data ]);

            }

        }

    }

    private function checkPermissions($query)
    {

        global $wgBioDBApi;
        global $wgBioDBExpose;

        // Default behaviour
        $APIallow = $wgBioDBApi;

        if (array_key_exists("api", $wgBioDBExpose[$query])) {

            $APIallow = false; // Once api defined, be restrictive

            $apiGroups = $wgBioDBExpose[$query]["api"];

            $groups = $this->getUser()->getGroups();

            if (in_array("*", $apiGroups)) {
                $APIallow = true;
            }

            foreach ($groups as $group) {
                if (in_array($group, $apiGroups)) {
                    $APIallow = true;
                }
            }

        }

        return $APIallow;

    }

    private function processTyping($data)
    {

        $newdata = [ ];

        foreach ($data as $row) {

            $newstruct = [];

            foreach ($row as $prop => $value) {

                $newstruct[$prop] = $this->fixType($value);

            }

            array_push($newdata, $newstruct);

        }

        return $newdata;

    }

    private function fixType($value)
    {

        if (is_numeric($value)) {
            $intvalue = intval($value);

            if ($intvalue == $value) {
                $value = $intvalue;
            } else {
                $value = floatval($value);
            }
        }

        return $value;

    }

    public function getAllowedParams()
    {
        return [
            'query' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-query',
            ],
            'param' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-param',
            ],
            'table' => [
                ApiBase::PARAM_TYPE => 'boolean',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-table',
            ],
            'fileformat' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-fileformat',
            ],
            'sep' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-sep',
            ],
            'typesolve' => [
                ApiBase::PARAM_TYPE => 'boolean',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_HELP_MSG => 'apihelp-biodb-param-typesolve',
            ],
        ];
    }
}
