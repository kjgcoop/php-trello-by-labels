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

use Trello\Client;
use GetTrelloLabels;


class TrelloLabelBatch {

    /**
     * @var string Client This connects to Trello. The class is defined in
     *     cdaguerre/php-trello-api
     *
     * @link https://github.com/cdaguerre/php-trello-api/
     */
    protected $client;


    /**
     * @var string The API key given by Trello
     *
     * @link https://trello.com/app-key
     */
    protected $api_key;


    /**
     * @var string The API key token by Trello
     *
     * @link https://trello.com/app-key
     */
    protected $api_token;

    /**
     * @var string The user whose data we want to grab. Presumably you.
     */
    protected $user;

    /**
     * @var array All the labels across all boards as TrelloLabel objects
     */
    protected $labels = [];

    /**
     * @var array All boards as TrelloBoard objects
     */
    protected $boards = [];

    /**
     * The constructor requires Trello crdentials so it can open the initial
     * connection. It doesn't retrieve any data. Data retrieved in
     * $this->populate().
     *
     * @param string $api_key The API key given to you by Trello
     * @param string $api_token The API token given to you by Trello
     * @param string $user The name of the user whose data you'll be
     *     retrieving. Presumably you
     *
     * @return void
     *
     * @link https://trello.com/app-key
     *
     * @see TrelloLabelBatch::populate
     */
    public function __construct($api_key, $api_token, $user) {
        $this->api_key   = $api_key;
        $this->api_token = $api_token;

        $this->user = $user;

        if ($this->ready_for_connection()) {
            echo 'Connecting to Trello as user '.$this->user."\n";

            try {
                $this->client = new Client();
                $this->client->authenticate($this->api_key, $this->api_token, Client::AUTH_URL_CLIENT_ID);
            } catch (Exception $e) {
                throw new Exception('Unable to authenticate: '.$e->getMessage());
            }


        } else {
            throw new Exception('No Trello credentials given');
            //
        }
    }

    /**
     * Get all the labels for this account in Trello. Must be preceded by a call
     * to $this->populate() or there will be no data.
     *
     * @return array List of labels
     *
     * @see TrelloLabelBatch::populate
     */
    public function get_labels() {
        return $this->labels;
    }

    /**
     * Grab all the labels and cards from Trello. Mostly ignores boards. Should
     * be called immediately after the constructor.
     *
     * @return void
     *
     * @todo Add to constructor? Since the rest of the functions here are pretty
     * useless until this gets called.
     */
    public function populate() {

        try {
            $all_boards = $this->client->api('member')->boards()->all($this->user, array('filter' => 'open'));
        } catch (Exception $e) {
            throw new Exception('Unable to retrieve boards');
        }

        $this->merged_labels = array();

        foreach ($all_boards as $board_arr) {

            try {
                // Stash the raw data into a formal object
                $board = new TrelloBoard($board_arr);
                $board->get_cards($this->client);
            } catch (Exception $e) {
                throw new Exception('Unable to get cards on board '.$board->get_name()."\n");
            }

            echo "\nBOARD NAME: ".$board->get_name()."\n";

            // Populate $this->labels - this function both adds this board to
            // the list of those affected by the label in question and also
            // adds the new labels to the local hash
            $this->record_distinct_labels($board);

            // Put the formal object in our list.
            $this->boards[] = $board;

        }
    }

    /**
     * Begin private parts -----------------------------------------------------
     */

    /**
     * This indicates whether or not this object has the information it needs to
     * connect to Trello
     *
     * @return bool Indicating whether or not this object is ready
     * to connect to Trello
     */
    private function ready_for_connection() {
        return $this->api_key !== '' && $this->api_token !== '' && $this->user !== '';
    }

    /**
     * Labels are stored in  a has, so there can be room for one one distinct
     * label. However, there are presumably many cards with that label.
     * When we have a duplicate, we need to bring its cards into our object.
     *
     * @param TrelloLabel $label The object to be added
     *
     * #return void
     */
    private function add_label(TrelloLabel $label) {
        // A label object is just a name and color with a list of cards. If we
        // already have a label in this spot in the hash, pull in all the cards
        // from the new guy.
        if (isset($this->labels[$label->get_key()])) {
            $this->labels[$label->get_key()]->eat_label($label);

        } else {
            $this->labels[$label->get_key()] = $label;
        }
    }

    /**
     * Loop through all the board labels on a board and bring them into local
     * memory.
     *
     * @param TrelloBoard $board The board whose labels we want
     *
     * @return void
     */
    private function record_distinct_labels(TrelloBoard $board) {

        $cards = $board->get_cards();

        // Populate our list of labels with those grabbed from cards. The board
        // has its own list, but it potentially included unused labels.
        foreach ($cards as $card) {
            $labels = $card->get_labels();

            foreach ($labels as $label) {
                $label->add_board($board);
                $label->add_card($card);
                $this->add_label($label);

                echo "\tAdding label ".$label->get_key()."\n";
            }
        }
    }
}
