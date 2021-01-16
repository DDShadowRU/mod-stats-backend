<?php

namespace ddcompany;

use Exception;

class APIHandler
{
    /**
     * @var IAPI[]
     */
    private $actions = [];

    public function __construct()
    {
        $this->actions["mod"] = new APIStats();
        $this->actions["dump"] = new APIDump();
        $this->actions["authors"] = new APIAuthors();
    }

    function handle(array $params)
    {
        header('Content-Type: application/json');
        $action = $this->actions[$params["action"]];
        if (!$action) {
            $this->fall("Undefined action");
        }

        try {
            $action->run();
        } catch (Exception $e) {
            $this->fall($e->getMessage());
        }
    }

    function fall(string $msg)
    {
        echo json_encode(["error" => $msg]);
        exit();
    }
}