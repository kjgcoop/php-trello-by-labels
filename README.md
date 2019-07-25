# trello-label-package

Trello's labels are per board, so you can't see all the tasks with a given label. Rather than hopping between boards, you can use this script to loop through the labels.

## Getting Started

```
// Give the object enough information to connect to Trello
$batch = new TrelloLabelBatch([Trello API key], [Trello API token], [Trello user whose data you're interested in]);

// Grab the data
$batch->populate();

// You may now work with the data.

foreach ($batch->get_labels() as $label) {
    foreach ($label->get_cards() as $card) {
        // Whatever you want to do.
    }
}
```

The assumes that you only want to deal in one user's boards.

## Notes on Data

A populated batch represents an array of all your labels, each containing the relevant cards.
 * A TrelloLabel object has a color, a name and a list of cards
 * A TrelloCard object has a name, board name and list of labels.

Any given card may appear any number of times.
 * If a card has no labels, it won't appear in the data structure.
 * If a card has more than one label applied, the card will appear in memory once per label.

A label without any cards won't appear - while Trello boards have a list of its labels, this generates its list by looping through existing cards.

## Prerequisites

PHP and the PHP Curl Extension and Composer. All the other dependencies are handled by Composer.

### Built With

* [cdaguerre/php-trello-api](https://github.com/cdaguerre/php-trello-api) - The Trello API class this code relies on. Thanks, cdaguerre!

## License

This is licensed under the [GPL License](https://www.gnu.org/licenses/gpl-3.0.html)
