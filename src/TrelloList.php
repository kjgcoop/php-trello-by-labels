<?php
namespace GetTrelloLabels;

/**
 * This isn't in use yet.
 */
class TrelloList {

    protected $parent_board;

    protected $cards = [];

    public function __construct(TrelloBoard $parent_board) {
        $this->parent_board = $parent_board;
    }
}
