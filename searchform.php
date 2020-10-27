<form role="search" method="get" id="searchform" class="searchform" action="/">
<label for="search">Search</label>
    <input type="text" id="search" name="s" value="<?php the_search_query(); ?>" />
    <input type="submit" id="searchsubmit" value="Search" />
</form>