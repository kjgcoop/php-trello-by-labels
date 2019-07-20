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

/**
 * This class is used a lot less in the branch that won the race to master.
 * There's probably a fair amount of irrelevant code in here. I'm keeping it
 * it around because the odds are good it'll become relevant again in some
 * future version.
 */
class TrelloBoard {

    /**
     * @var array Raw data returned by Trello. See the bottom of this file for
     *     an example
     */
    protected $raw_board_array;


    /**
     * @var array TrelloCard objects representing the cards in this board.
     */
    protected $cards   = [];

    /**
     * @var array TrelloLabel objects representing the labels on this board.
     */
    protected $labels  = [];

    /**
     * Create a representation of a Trello board.
     *
     * @param array $raw_board_array The raw data returned by Trello
     *
     * @return void
     */
    public function __construct(Array $raw_board_array) {
        $this->raw_board_array = $raw_board_array;

        // Populate it's array of names and colors into a local list of
        // TrelloLabel objects.
        $this->merge_labels_in();
    }

    /**
     * Get the ID of this board
     *
     * @return string ID of this board
     */
    public function get_id() {
        return $this->raw_board_array['id'];
    }

    /**
     * Get the name of this board
     *
     * @return string The name of this board
     */
    public function get_name() {
        return $this->raw_board_array['name'];
    }

    /**
     * A list of labels on this board - populated in the constructor
     *
     * @return array TrelloLabels on this board
     */
    public function get_labels() {
        return $this->labels;
    }

    /**
     * Get label names - these are in the form name => color.
     *
     * @return array Board names and colors hashed by name
     */
    public function get_label_names() {
        return $this->raw_board_array['labelNames'];
    }

    /**
     * Get the array of TrelloCard objects - may have to query Trello for the
     * raw data.
     *
     * @param Client|null $client This connects to Trello. The class is defined
     *     in cdaguerre/php-trello-api. Ths is optional - we may not want to
     *     query Trello right now.
     *
     * @link https://github.com/cdaguerre/php-trello-api/
     *
     * @return array of TrelloCard objects
     */
    public function get_cards($client = null) {

        if ($client != null) {
            echo "Calling Trello for cards\n";
            $this->get_raw_cards($client);
        }

        return $this->cards;
    }

    /**
     * Return all the cards with the label $label
     *
     * @param TrelloLabel $label The cards should have this label
     *
     * @return array Topical TrelloCard objects.
     *
     * @todo Send in just the label's key. Would need to also alter has_label
     */
    public function get_cards_with_label(TrelloLabel $label) {
        $topical_cards = array();

        foreach ($this->cards as $card) {
            if ($card->has_label($label)) {
                $topical_cards[] = $card;
            }
        }

        return $topical_cards;
    }

    /**
     * Get the cards hot off the Trello press. This only sets the local
     * card array - it doesn't return any data
     *
     * @param Client $client This connects to Trello. The class is defined in
     *     cdaguerre/php-trello-api
     *
     * @link https://github.com/cdaguerre/php-trello-api/
     *
     * @return void
     *
     * @todo It probably should return data, what with get right there in the
     *     name.
     */
    private function get_raw_cards($client) {

        // @todo Will this return archived? Do not want.
        $all_cards = $client->api('board')->cards()->all($this->get_id());
        $this->cards = array();

        foreach ($all_cards as $card_arr) {
         //   $this->cards[] = new TrelloCard($card_arr);
            $card = new TrelloCard($card_arr);
            $card->set_board_name($this->get_name());
            $this->cards[] = $card;
        }
    }

    /**
     * Add a label to this board. It already has an array of name => color
     * pairs, this will add a TrelloLabel object
     *
     * @param TrelloLabel $label The label object to be added
     *
     * @return void
     */
    public function add_label(TrelloLabel $label) {
        $this->labels[] = $label;
    }

    /**
     * Take the name => color pairs and put them in $this->labels as TrelloLabel
     * objects
     *
     * @return void
     */
    public function merge_labels_in() {
        foreach ($this->get_label_names() as $color => $name) {

            // Set name after color because the name might be blank and we
            // need to fall back on the color's name.
            $label = new TrelloLabel($color, $name);

//                $this->merged_labels[$name][$board->get_name()] = $this->hash_labels_by_name($label, $board);
            $this->add_label($label);
        }

    }
}


/* Output of print_r of raw board array:

    Array
    (
        [name] => Party
        [desc] =>
        [descData] =>
        [closed] =>
        [idOrganization] => 5c9ef357d8d8b512f37df0fd
        [limits] =>
        [pinned] =>
        [shortLink] => hQyNgdrd
        [powerUps] => Array
            (
            )

        [dateLastActivity] => 2019-04-25T14:32:55.173Z
        [idTags] => Array
            (
            )

        [datePluginDisable] =>
        [creationMethod] =>
        [ixUpdate] =>
        [id] => 5c33dc542153e253c981287d
        [starred] =>
        [url] => https://trello.com/b/hQyNgVrf/party
        [prefs] => Array
            (
                [permissionLevel] => private
                [hideVotes] =>
                [voting] => disabled
                [comments] => members
                [invitations] => members
                [selfJoin] =>
                [cardCovers] => 1
                [isTemplate] =>
                [cardAging] => regular
                [calendarFeedEnabled] =>
                [background] => 5c05de865f30b95960df1f35
                [backgroundImage] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/2560x1707/510f83976efa2746ac68ab2ee8f7338f/photo-1526304640581-d334cdbbf45d
                [backgroundImageScaled] => Array
                    (
                        [0] => Array
                            (
                                [width] => 140
                                [height] => 93
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/140x93/f3fbff4bc5cac8c181811fbc9de7885c/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [1] => Array
                            (
                                [width] => 256
                                [height] => 171
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/256x171/3f5e56b4493aa71b4511fd85e571032e/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [2] => Array
                            (
                                [width] => 480
                                [height] => 320
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/480x320/1ea5187d3c99aa299280f63a66c685df/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [3] => Array
                            (
                                [width] => 960
                                [height] => 640
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/960x640/5aed5d9d933bd9aac349ff85e285372f/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [4] => Array
                            (
                                [width] => 1024
                                [height] => 683
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/1024x683/ad18f0a22e6a4812c80f7c6730cdc492/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [5] => Array
                            (
                                [width] => 2048
                                [height] => 1366
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/2048x1366/35afdc8563b7e68758fe4a3370b093f9/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [6] => Array
                            (
                                [width] => 1280
                                [height] => 854
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/1280x854/b2ecdbb0e618d7241b8f9468cccc013a/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [7] => Array
                            (
                                [width] => 1920
                                [height] => 1280
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/1920x1280/de82579ed63a1c89effa19f5a3534a07/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [8] => Array
                            (
                                [width] => 2400
                                [height] => 1600
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/2400x1600/43fa1fccb03a5b0fd8f14818cbbf9626/photo-1526304640581-d334cdbbf45d.jpg
                            )

                        [9] => Array
                            (
                                [width] => 2560
                                [height] => 1707
                                [url] => https://trello-backgrounds.s3.amazonaws.com/SharedBackground/2560x1707/510f83976efa2746acf8ab2ee8f7338f/photo-1526304640581-d334cdbbf45d
                            )

                    )

                [backgroundTile] =>
                [backgroundBrightness] => light
                [backgroundBottomColor] => #8ea0aa
                [backgroundTopColor] => #869ba7
                [canBePublic] => 1
                [canBeEnterprise] => 1
                [canBeOrg] => 1
                [canBePrivate] => 1
                [canInvite] => 1
            )

        [subscribed] =>
        [labelNames] => Array
            (
                [green] => Expense
                [yellow] =>
                [orange] => Call
                [red] => During work
                [purple] =>
                [blue] => Big computer
                [sky] =>
                [lime] =>
                [pink] =>
                [black] =>
            )

        [dateLastView] => 2019-04-25T14:32:55.211Z
        [shortUrl] => https://trello.com/b/hQyNgVfD
        [memberships] => Array
            (
                [0] => Array
                    (
                        [id] => 5c33dc542153e253cf812877
                        [idMember] => 5c2ad64a18cf65709ae196af
                        [memberType] => admin
                        [unconfirmed] =>
                        [deactivated] =>
                    )

            )

    )


 */
