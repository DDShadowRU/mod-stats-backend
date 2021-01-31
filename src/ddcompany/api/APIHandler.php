<?php

namespace ddcompany\api;

use Exception;

class APIHandler
{
    /**
     * @var IAPI[]
     */
    private $actions = [];

    public function __construct()
    {
        $this->actions["mod/{id}"] = new APIMod();
        $this->actions["author/{id}"] = new APIAuthor();
        $this->actions["authors"] = new APIAuthors();
        $this->actions["mods"] = new APIMods();
        $this->actions["dump"] = new APIDump();
    }

    function handle()
    {
        header('Content-Type: application/json');
        $uri = substr($_SERVER["REDIRECT_URL"], 1);
        $parts = preg_split("[/]", $uri);
        $partsCount = count($parts);

        foreach ($this->actions as $key => $action) {
            $actionParts = preg_split("[/]", $key);
            if ($partsCount !== count($actionParts)) {
                continue;
            }

            $params = [];
            foreach ($actionParts as $actionPartKey => $actionPart) {
                if ($partsCount < $actionPartKey) {
                    continue 2;
                }

                $matches = [];
                if (preg_match("/[{](\w+)[}]/", $actionPart, $matches)) {
                    $params[$matches[1]] = $parts[$actionPartKey];
                } else if ($actionPart != $parts[$actionPartKey]) {
                    continue 2;
                }
            }


            try {
                $action->run($params);
            } catch (Exception $e) {
                $this->fall($e->getMessage());
            }
            return;
        }

        $this->fall("Undefined action");
    }

    function fall(string $msg)
    {
        echo json_encode(["error" => $msg]);
        exit();
    }
}