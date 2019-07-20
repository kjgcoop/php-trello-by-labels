<?php
/**
 * @package php-trello-by-labels
 * @author KJ Coop <code@kjcoop.com>
 * @copyright 2019 KJ Coop
 *
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link https://github.com/kjgcoop/php-trello-by-labels
 */

namespace GetTrelloLabels;

class TrelloLabel {

    /**
     * @var string The name of the card. Might be blank, in which case
     * we treat the color as the name.
     *
     * Trell allows labels without a color. I ass-u-me Trello has a rule that a
     * have either a color or a name.
     *
     * @todo  Find out if Trello allows labels to be namelss and colorless.
     *
     */
    protected $name;

    /**
     * @var string The color assigned to the card.
     */
    protected $color;


    /**
     * @var array The cards that have this label
     */
    protected $cards  = [];


    /**
     * Other objects save the raw data dump from Trello, but this doesn't. For
     * the purposes of this script, we consider a label distinct if it has the
     * same name and color. Trello also returns other data such as an ID.
     * There's a race condition on that other data, so don't bother saving it.
     *
     * If you're reading this documentation (which, thank you!), you probably
     * know what a race condition is. In case you don't:
     *
     * Consider the possibility of Board A having green Label 1 with ID abc and
     * Board B also having green Label 1 at ID def. When TrelloLabelBatch comes
     * across green Label 1 in Board A, it would save the ID as abc. Later it
     * encounters Board B and replaces the ID with def. Then along comes some
     * innocent soul who is not dedicated to documentation the way you are,
     * gentle reader, and tries to get details about green Label 1. He'll spend
     * a long time debugging. He may even resort to reading this. Let's spare
     * him that suffering.
     */
//    protected $raw_label_array;


    /**
     * Create an instance. Either the color or the name might be blank (@todo
     * both?), but it's more likely the name. If name is null, use the color
     * as the name
     *
     * @var string color The color of the label - might be blank, but not likely
     * @var string name The name of the label - might be blank
     *
     * @todo When I wrote this, the possibility of a blank color hadn't occured
     *     to me. Tweak the setters to attempt to handle a blank card.
     *
     * @todo Confirm my assumption that Trello requires a name or a color
     */
    public function __construct($color, $name) {
        // Use setters here to set the name and color so it can account for
        // blanks. When I first wrote this, it hadn't dawned on me that a color
        // might also be blank, so there may be some weird around that. See the
        // Function-level comments.
        $this->set_color($color);
        $this->set_name($name);
    }

    /**
     * Note a card associated with this label.
     *
     * @param TrelloCard $card The card object to be added
     *
     * @return void
     */
    public function add_card($card) {
        $this->cards[] = $card;
    }

    /**
     * This is called if we have two label objects with the same key vying for
     * a spot in a hash. Merge the newcomer's cards and boards into this one.
     *
     * @param TrelloLabel $label The newcomer whose distinctiveness we will add
     * to our own.
     *
     * @return void
     */
    public function eat_label(TrelloLabel $label) {
        $this->add_cards($label->get_cards());
        $this->add_boards($label->get_boards());
    }


    /**
     * Add an array of cards to this one. I think this is used exclusively by
     * $this->eat_label().
     *
     * @var array $cards An array of TrelloCard objects to be added to this
     *     instance's list of cards
     *
     * @return void
     *
     * @todo Scope private?
     */
    public function add_cards(array $cards) {
        $this->cards = array_merge($this->cards, $cards);
    }

    /**
     * Grab the cards associted with this label.
     *
     * @return array List of TrelloCard objects
     */
    public function get_cards() {
        return $this->cards;
    }

    /**
     * A board that contains this label.
     *
     * @param TrelloBoard The board to add
     */
    public function add_board(TrelloBoard $board) {
        $this->boards[$board->get_name()] = $board;
    }

    /**
     * Note boards where this label can be found.
     *
     * @param array List of TrelloBoard objects
     *
     * @todo I think this is only use by eat_label. Scope private?
     */
    public function add_boards(array $boards) {
        foreach ($boards as $board) {
            $this->boards[$board->get_name()] = $board;
        }
    }

    /**
     * Get all the boards that host this label
     *
     * @return array TrelloBoard objects
     */
    public function get_boards() {
        return $this->boards;
    }

    /**
     * Set the color. Either color or name might be blank, but when I wrote this
     * the possibility of a blank color hadn't occurred to me. For the sake of
     * the rest of this bit of documentation, set aside that possibility:
     *
     * A name might be blank, so set the name equal to the color. The real name,
     * should one come along, will clobber the color-name.
     *
     * @param string $color Color of the label; to be temporarily used as the
     *     name of the label also, unless the name has already been explicitly
     *     set.
     *
     * @return void
     *
     * @todo Account for blank color/name pairs.
     */
    public function set_color($color) {
        $this->color = $color;

        // If name is set before color, a blank name could slip through. If name
        // is blank, toss the color in as the name for now.
        if ($this->name == '') {
            $this->name = $color;
        }
    }

    /**
     * Set the name. Either color or name might be blank, but when I wrote this,
     * the possibility of a blank color hadn't occurred to me. For the sake of
     * the rest of this bit of documentation, set aside that possibility:
     *
     * If the name is blank, set it equal to the color.
     *
     * @param string $name Name of the label; if blank, will be replaced by
     *     the label's color.
     *
     * @return void
     *
     * @todo Account for blank color/name pairs.
     */
    public function set_name($name) {
        if ($name == '') {
            $this->name = $this->color;
        } else {
            $this->name = $name;
        }
    }

    /**
     * Get the color of this label
     *
     * @return string The color of this label
     */
    public function get_color() {
        return $this->color;
    }

    /**
     * Get the name of this label
     *
     * @return string The name of this label
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Labels are hased everywhere, get the key by which to hash them. As of
     * this writing, it's color-name.
     *
     * @return string color-name
     */
    public function get_key() {
        return $this->get_color().'-'.$this->get_name();
    }

/*
This is what a raw label array from Trello looks like. We never see this, but I
thought it might be interesting.
    Array
    (
        [id] => 5c78e08bb0093a309723261f
        [idBoard] => 5c30538fafd8ea23cff04149
        [name] => Big computer
        [color] => blue
    )
*/

}

