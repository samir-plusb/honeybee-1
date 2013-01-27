<?php
    $routeName = $t['route_name'];
    $currentOffset = $t['offset'];
    $pagingRange = $t['paging_range'];
    $totalCount = $t['total_count'];
    $totalPages = $t['total_pages'];
    $limit = $t['limit'];
    $currentPage = $t['current_page'];
    $lastPage = $t['last_page'];
    $hasPrev = $t['has_previous'];
    $hasNext = $t['has_next'];
    $onFirstPage = $t['first_page_reached'];
    $onLastPage = $t['last_page_reached'];

    $firstLink = $t['links']['first_page'];
    $secondLink = $t['links']['second_page'];
    $lastLink = $t['links']['last_page'];
    $secondLastLink = $t['links']['second_last_page'];
    $prevLink = $t['links']['previous_page'];
    $nextLink = $t['links']['next_page'];

    $searchPhrase = isset($t['search']) ? $t['search'] : FALSE;
    $filter = isset($t['filter']) ? $t['filter'] : FALSE;
    $sorting = isset($t['sorting']) ? $t['sorting'] : FALSE;

// Calculate the range of pages to place in the middle.
    $startAt = 0;
    $endAt = 0;
    $centerOffset = (int)floor($pagingRange / 2);
    if (0 < $pagingRange)
    {
        $startAt = $currentPage - $centerOffset;
        if (1 >= $currentPage - $centerOffset)
        {
// Correct start pos if we are in the first pagination segment.
            $startAt = 2;
        }
        elseif ($lastPage - 1 <= $currentPage + $centerOffset)
        {
// Correct start pos if we are in the last pagination segment.
            $startAt = $lastPage - $pagingRange - 1;
        }
        $endAt = $startAt + $pagingRange;
    }
?>
<nav class="pagination well">
    <ul>
<?php
    if ($hasPrev)
    {
// if there is a previous page, display the "previous page" link.
?>
        <li class="prev">
            <a href="<?php echo $prevLink; ?>">&#8592; Previous</a>
        </li>
<?php
    }
    else
    {
// otherwise disable it.
?>
        <li class="prev disabled">
            <a>&#8592; Previous</a>
        </li>
<?php
    }
    if (1 < $totalPages)
    {
// always render a "first page" link if there is more than one page.
?>
        <li class="<?php echo $onFirstPage ? 'active' : ''; ?>">
<?php
        if ($onFirstPage)
        {
?>
            <a>1</a>
<?php
        }
        else
        {
?>
            <a href="<?php echo $firstLink; ?>">1</a>
<?php
        }
?>
        </li>
<?php
    }

    if (2 === $startAt && 2 < $totalPages || (2 < $totalPages && 6 > $totalPages))
    {
// if we have at least 3 pages we can display a "second page" link,
// when the current page is within the first pagination segment.
?>

        <li class="<?php echo (1 === $currentPage) ? 'active' : ''; ?>">
<?php
        if (1 === $currentPage)
        {
?>
            <a>2</a>
<?php
        }
        else
        {
?>
            <a href="<?php echo $secondLink; ?>">2</a>
<?php
        }
?>
        </li>
<?php
    }
    elseif (5 < $totalPages)
    {
// with at least 6 pages we also check for the "..." placeholder.
?>
        <li class="disabled">
            <a>...</a>
        </li>
<?php
    }

    for ($curPage = $startAt; $curPage < $endAt; $curPage++)
    {
        $routeData = array(
            'limit'  => $limit,
            'offset' => $curPage * $limit
        );
        if ($searchPhrase)
        {
            $routeData['search'] = $searchPhrase;
        }
        else if ($filter)
        {
            $routeData['filter'] = $filter;
        }
        if ($sorting)
        {
            $routeData['sorting'] = $sorting;
        }
        $link = $ro->gen($routeName, $routeData);
        $active = ($curPage === $currentPage);
?>
        <li class="<?php echo $active ? 'active' : ''; ?>">
<?php
        if ($active)
        {
?>
            <a><?php echo ($curPage + 1); ?></a>
<?php
        }
        else
        {
?>
            <a href="<?php echo $link; ?>"><?php echo ($curPage + 1); ?></a>
<?php
        }
?>
        </li>
<?php
    }

    if ($lastPage - 1 === $endAt && 3 < $totalPages || (3 < $totalPages && 6 > $totalPages))
    {
// if we have at least 4 pages we can display a "second-last page" link,
// when the current page is within the last pagination segment.
?>
        <li class="<?php echo ($lastPage - 1) === $currentPage ? 'active' : ''; ?>">
<?php
        if ($lastPage - 1 === $currentPage)
        {
?>
            <a><?php echo $lastPage; ?></a>
<?php
        }
        else
        {
?>
            <a href="<?php echo $secondLastLink; ?>"><?php echo $lastPage; ?></a>
<?php
        }
?>
        </li>
<?php
    }
    elseif (5 < $totalPages)
    {
// with at least 6 pages we also check for the "..." placeholder.
?>
        <li class="disabled">
            <a>...</a>
        </li>
<?php
    }

    if (1 < $totalPages)
    {
// always render a "last page" link if there are at least two pages.
?>
        <li class="<?php echo $onLastPage ? 'active' : ''; ?>">
<?php
        if ($onLastPage)
        {
?>
            <a><?php echo $lastPage + 1; ?></a>
<?php
        }
        else
        {
?>
            <a href="<?php echo $lastLink; ?>"><?php echo $lastPage + 1; ?></a>
<?php
        }
?>
        </li>
<?php
    }
// if there is a next page, display the "next page" link.
    if ($hasNext)
    {
?>
        <li class="next">
            <a href="<?php echo $nextLink; ?>">Next &#8594;</a>
        </li>
<?php
    }
    else
    {
// otherwise disable it.
?>
        <li class="next disabled">
            <a>Next &#8594;</a>
        </li>
<?php
    }
?>
    </ul>
</nav>