<?php

class LatestPagesVisitedWidget extends Widget
{
    public static $db = array();

    public static $number_of_pages_back = 21;

    public static function setNumberOfPagesBack($val)
    {
        if ($val > 0 && $val < 99) {
            self::$number_of_pages_back = $val;
        }
    }

    public static $title = "Latest Pages Visited";
    public static $cmsTitle = "Latest Pages Visited";
    public static $description = "Shows the latest pages visited by the user in reverse chronological order";

    public static function addPage()
    {
        $historyString = Session::get("LastPagesVisited");
        $historyArray = unserialize($historyString);
        if (!is_array($historyArray)) {
            $historyArray = array();
        }
        $page = Director::currentPage();
        if ($page) {
            $pageID = $page->ID;
            if ($pageID) {
                if (!in_array($pageID, $historyArray)) {
                    array_unshift($historyArray, $pageID);
                    if (count($historyArray) > self::$number_of_pages_back) {
                        array_pop($historyArray);
                    }
                } elseif ($historyArray[count($historyArray)-1] == $pageID) {
                    array_pop($historyArray);
                    array_unshift($historyArray, $pageID);
                }
            }
        }
        Session::set("LastPagesVisited", serialize($historyArray));
    }

    public static function history($pagesBack = 1)
    {
        $array = unserialize(Session::get("LastPagesVisited"));
        return $array[$pagesBack];
    }

    public function LatestPages()
    {
        //copy from Director
        $historyArray = unserialize(Session::get("LastPagesVisited"));
        //Session::addToArray('history', substr($_SERVER['REQUEST_URI'], strlen(Director::baseURL())));
        $DOS = new DataObjectSet();
        //note that we go beyond number_of_pages_back as people may visit the same page twice
        for ($i = 1; $i < self::$number_of_pages_back; $i++) {
            if (isset($historyArray[$i])) {
                $pageID = $historyArray[$i];
                if (intval($pageID)) {
                    $page = DataObject::get_by_id("SiteTree", $pageID);
                    if ($page) {
                        $DOS->push($page);
                    }
                }
            }
        }
        return $DOS;
    }
}
