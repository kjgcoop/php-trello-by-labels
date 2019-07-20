# trello-label-package

Trello's labels are per board, so you can't see all the tasks with a given label, you have to hop per board. This script gather tasks hashed by label. Presently, it's just rough exploring that outputs details to the command line.

The output will consist of an array of labels. Within each, there's a list of each board that has a label by that name. It tells you what color the label is on that board, and the cards associated with it.

This is built with [cdaguerre/php-trello-api](https://github.com/cdaguerre/php-trello-api) - many thanks to cdaguerre!

## Getting Started

For the API key and token and username, it looks for a file called config that's formatted as follows:

```
<?php

$api_key   = 'Your key';
$api_token = 'Your token';
$user      = 'You. Or whoever.';


```

The script is predicated on the assumption that you only want to deal in one user's boards.


### Prerequisites

PHP, the PHP Curl Extension, the GD library; all the other dependencies are handled by Composer:

### Running it

From the command line, run:

```php get_labels.php```

PHP will print_r the results.


### License

This is licensed under the [Affero License](https://www.gnu.org/licenses/agpl-3.0.en.html)
