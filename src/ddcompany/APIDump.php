<?php


namespace ddcompany;


class APIDump extends AbstractAPI
{
    private static $lockFile = ".dump-lock";
    private static $logFile = "dump_log.txt";

    function run()
    {
        parent::run();
        if ($_GET["s"] !== $_ENV["SECRET"]) {
            $this->fall("No, man");
        }

        $this->lockIfNeeded();
        ignore_user_abort(true);
        $mods = $this->getMods();
        try {
            $db = MySqlHelper::connect();
//            if (date("d") === "01") {
//                $this->log("Truncate table...");
//                if (!$db->query("TRUNCATE `downloads`") || !$db->query("TRUNCATE `likes`")) {
//                    $this->logAndFall("Invalid truncate table: $db->error");
//                }
//            }

            if (!$db->query("TRUNCATE `authors`")) {
                $this->log("Truncate authors error: $db->error\n");
            }

            $authors = [];
            foreach ($mods as $mod) {
                $data = $this->fetchDataFor($mod->id);
                if (!$data) {
                    continue;
                }

                $comments = count($data->comments);
                if (!$db->query("INSERT INTO `stats`(`mod_id`, `date`, `downloads`, `likes`, `comments`, `author`)
                                  VALUES ('$mod->id', now(), '" . $data->downloads . "', '" . $data->likes . "', '" . $comments . "', '" . $data->author . "')")) {
                    $this->log("Insert error for '$mod->id': $db->error\n");
                }

                $author = $data->author;
                if (!in_array($author, $authors)) {
                    $authors[] = $author;
                    if (!$db->query("INSERT INTO `authors`(`id`,`name`) VALUES ('" . $author . "','" . $data->author_name . "')")) {
                        $this->log("Author insert error: $db->error\n");
                    }
                }
            }

            $db->close();
            $this->log("Success at " . gmdate("M d Y H:i:s") . ". Fetched " . count($mods) . " mods in " . $this->getRunTime() . "s");
        } catch (MySqlException $exception) {
            $this->log("Mysql Error:" . $exception->getMessage());
        }

        unlink(self::$lockFile);
        $this->cancel([]);
    }

    private function fetchJson($url)
    {
        $response = file_get_contents($url);
        return $response ? json_decode($response) : null;
    }

    private function fetchDataFor($modId)
    {
        $data = $this->fetchJson("https://icmods.mineprogramming.org/api/description?id=$modId&lang=en&comments_limit=-1");
        if (!$data) {
            $this->log("Invalid to fetch mod description for '$modId'");
            return 0;
        }

        return $data;
    }

    private function getMods()
    {
        $amount = $this->getModsAmount();
        if (!$amount) {
            $this->log("Invalid 'api/count' request");
            exit();
        }

        $mods = $this->fetchJson("https://icmods.mineprogramming.org/api/list?lang=en&start=0&count=$amount&sort=popular&horizon");
        if (!$mods) {
            $this->logAndFall("Invalid 'api/list' json");
        }

        return $mods;
    }

    private function getModsAmount()
    {
        return $this->fetchJson("https://icmods.mineprogramming.org/api/count?horizon");
    }

    private function isDumpLocked(): bool
    {
        return file_exists(self::$lockFile);
    }

    private function logAndFall($msg)
    {
        $this->log($msg);
        $this->fall($msg);
    }

    private function log(string $msg)
    {
        file_put_contents(self::$logFile, $msg . "\n", FILE_APPEND);
    }

    private function lockIfNeeded()
    {
        if ($this->isDumpLocked()) {
            $this->fall("Locked");
        }
        file_put_contents(self::$lockFile, " ");
    }
}