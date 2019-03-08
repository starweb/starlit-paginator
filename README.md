# Paginator

[![Build Status](https://travis-ci.org/starweb/starlit-paginator.svg?branch=master)](https://travis-ci.org/starweb/starlit-paginator)
[![Code Coverage](https://scrutinizer-ci.com/g/starweb/starlit-paginator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/starweb/starlit-paginator/?branch=master)

Generates pagination HTML.

## Installation
Add the package as a requirement to your `composer.json`:
```bash
$ composer require starlit/paginator
```

## Usage
```php
<?php

use Starlit\Paginator;

$currentPageNo = 1;
$rowsPerPage = 10;
$totalRowCount = 20;

$paginator = new Paginator(
    $currentPageNo,
    $rowsPerPage,
    $totalRowCount,
    function ($page) {
        return 'index.php?page=' . $page;
    }
);

echo $paginator->getHtml();
```

Produces:
```html
<div class="pagination multiple-pages">
    <ul>
        <li class="previous disabled"><span>&laquo;</span></li>
        <li class="active"><a href="index.php?page=1">1</a></li>
        <li><a href="index.php?page=2">2</a></li>
        <li class="next"><a href="index.php?page=2">&raquo;</a></li>
    </ul>
</div>
```


## Requirements
- Requires PHP 7.1 or above.

## License
This software is licensed under the BSD 3-Clause License - see the `LICENSE` file for details.
