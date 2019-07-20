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

class TrelloCard {

    /**
     * @var array Raw data returned by Trello. See the bottom of this file for
     *     an example
     */
    protected $raw_card_array = [];



    /**
     * @var array TrelloLabels associated with this card.
     */
    protected $labels = [];

    /**
     * @var string The name of the board this card belongs to. Not returned
     *     in the Trello API data
     */
    protected $board_name;

    /**
     * Create an object based on the raw data Trello's API sends
     *
     * @param array $raw_card_data The data dumped out by Trello's API
     *
     * @return void
     */
    public function __construct(Array $raw_card_array) {
        $this->raw_card_array = $raw_card_array;
    }

    /**
     * Get all the labels on a card
     *
     * @return array TrelloLabel objects
     */
    public function get_labels() {
        // The label array is empty if there are no labels or if it hasn't been
        // populated yet. If a card really has no labels, this will look for
        // labels anew each time this runs.
        if (empty($this->labels)) {

            $raw_labels = $this->raw_card_array['labels'];
            foreach ($raw_labels as $raw_label) {
                $label = new TrelloLabel($raw_label['color'], $raw_label['name']);
                $this->labels[$label->get_key()] = $label;
            }
        }

        return $this->labels;
    }

    /**
     * Get the name of this card. Not explicitly stored as a member variable,
     * grab from the raw data.
     *
     * @return string The name of this card
     */
    function get_name() {
        return $this->raw_card_array['name'];
    }

    /**
     * Get the name of the board this card is on. Not in the raw data Trello
     * sends, must be set by $this->set_board_name()
     *
     * @return string The name of the board this card is on.
     *
     * @see TrelloCard::set_board_name
     */
    function get_board_name() {
        return $this->board_name;
    }

    /**
     * The name of the board this card is on isn't returned in the raw data
     * Trello sends. Must be explicitly defined.
     *
     * @param string $board_name The name of the board this card is on.
     *
     * @return void
     */
    function set_board_name($board_name) {
        $this->board_name = $board_name;
    }

    /**
     * Check if a card has a given label. If there are no labels set, grab them
     * from the raw data sent by Trello. This will be run every time if there
     * really are no labels.
     *
     * @param TrelloLabel label being sought
     *
     * @return bool True if the given label is attached to the card.
     *
     * @see TrelloCard get_labels
     *
     * @todo Send in just the label key since that's all we need anyway
     */
    function has_label(TrelloLabel $label) {
        if (empty($this->labels)) {
            $this->get_labels(true);
        }

        return isset($this->labels[$label->get_key()]);
    }

/* The full array returned by Trello
Array
(
    [id] => 5c3053e83e7aca23bcca2cff
    [checkItemStates] =>
    [closed] =>
    [dateLastActivity] => 2019-01-23T07:30:54.000Z
    [desc] =>
    [descData] =>
    [dueReminder] =>
    [idBoard] => 5c30538fafd8ea23cd70414f
    [idList] => 5c3053955a1ec33092f920bf
    [idMembersVoted] => Array
        (
        )

    [idShort] => 3
    [idAttachmentCover] => 5c48182fddf4fc890929f74f
    [idLabels] => Array
        (
        )

    [manualCoverAttachment] =>
    [name] => Do laundry
    [pos] => 49153
    [shortLink] => Mu8IUPIf
    [badges] => Array
        (
            [attachmentsByType] => Array
                (
                    [trello] => Array
                        (
                            [board] => 0
                            [card] => 0
                        )

                )

            [location] =>
            [votes] => 0
            [viewingMemberVoted] =>
            [subscribed] =>
            [fogbugz] =>
            [checkItems] => 0
            [checkItemsChecked] => 0
            [comments] => 0
            [attachments] => 1
            [description] =>
            [due] =>
            [dueComplete] =>
        )

    [dueComplete] =>
    [due] =>
    [idChecklists] => Array
        (
        )

    [idMembers] => Array
        (
        )


    [labels] => Array
        (
            [0] => Array
                (
                    [id] => 5c78e0fbb0093a309723261f
                    [idBoard] => 5d30538fafd8ea23cf70414f
                    [name] => After work
                    [color] => blue
                )

        )

    [shortUrl] => https://trello.com/c/f8IUPIf
    [subscribed] =>
    [url] => https://trello.com/c/Mu8IUfIu/3-ff

 */

}






