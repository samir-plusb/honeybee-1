<?php
    $controllerOptions = $t['controller_options'];
    $itemData = $t['item_data']['masterRecord'];
    $ticketData = $t['ticket_data'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
    $directorWidgetOptions = $t['director_widget_opts'];
    $actorsWidgetOptions = $t['actors_widget_opts'];
?>
<div class="container-fluid controller-edit container-core-item"
     data-edit-controller-options='<?php echo $controllerOptions; ?>'>
<!-- controller notifications (optional section for displaying alerts that are added to the controller) -->
    <section class="container-alerts"
         data-bind="template: { foreach: alerts, afterAdd: showAlert, beforeRemove: hideAlert }">
        <div class="alert"
             data-bind="css: {
                            'alert-success': 'success' === type,
                            'alert-error': 'error' === type
                        }">
            <strong data-bind="text: message"></strong>
            <i class="icon-remove icon-white close"
               data-bind="click: function(alert) { $parent.removeAlert(alert); }"> </i>
        </div>
    </section>
    <form action="<?php echo $ro->gen(null); ?>" class="form-horizontal" method="post"
          data-bind="submit: onFormSubmit">
        <div class="row-fluid">
<!-- ###############################################################################################
Editing:
    Holds form for editing the current shofi place's coreItem. (All form values are nested here.)
############################################################################################### -->
            <section class="span6 editing">
                <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $ticketId; ?>" />
<!-- coreItem meta data -->
                <div class="well">
                    <div class="page-header" style="margin-top:0">
                        <h2>Filmdaten</h2>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Title</label>
                        <div class="controls">
                            <input type="input" name="movie[title]" value="<?php echo isset($itemData['title']) ? $itemData['title'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Subline</label>
                        <div class="controls">
                            <textarea name="movie[subline]"><?php echo isset($itemData['subline']) ? $itemData['subline'] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Beschreibung</label>
                        <div class="controls">
                            <textarea name="movie[teaser]"><?php echo isset($itemData['teaser']) ? $itemData['teaser'] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Direktor</label>
                        <section class="widget-tags-list widget-director controls"
                                  data-tags-list-options='<?php echo $directorWidgetOptions; ?>'>
                        </section>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Schauspieler</label>
                        <section class="widget-tags-list widget-actors controls"
                                  data-tags-list-options='<?php echo $actorsWidgetOptions; ?>'>
                        </section>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Website</label>
                        <div class="controls">
                            <input type="input" name="movie[website]" value="<?php echo isset($itemData['website']) ? $itemData['website'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Top-Film</label>
                        <div class="controls">
                            <input type="hidden" name="movie[isRecommendation]" value="0" />
                            <input type="checkbox" name="movie[isRecommendation]" value="1" 
                            <?php echo $itemData['isRecommendation'] ? 'checked="checked"' : '' ?> />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Verleih</label>
                        <div class="controls">
                            <input type="input" name="movie[rental]" value="<?php echo isset($itemData['rental']) ? $itemData['rental'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Genre</label>
                        <div class="controls">
                            <input type="input" name="movie[genre]" value="<?php echo isset($itemData['genre']) ? $itemData['genre'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Fsk</label>
                        <div class="controls">
                            <input type="input" name="movie[fsk]" value="<?php echo isset($itemData['fsk']) ? $itemData['fsk'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Land</label>
                        <div class="controls">
                            <input type="input" name="movie[country]" value="<?php echo isset($itemData['country']) ? $itemData['country'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Jahr</label>
                        <div class="controls">
                            <input type="input" name="movie[year]" value="<?php echo isset($itemData['year']) ? $itemData['year'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">L&auml;nge</label>
                        <div class="controls">
                            <input type="input" name="movie[duration]" value="<?php echo isset($itemData['duration']) ? $itemData['duration'] : ''; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Startdatum</label>
                        <div class="controls">
                            <input type="input" name="movie[releaseDate]" value="<?php echo isset($itemData['releaseDate']) ? $itemData['releaseDate'] : ''; ?>" />
                        </div>
                    </div>
                </div>
                <div class="form-actions well">
                    <input type="submit" value="Speichern" class="btn btn-primary" />
                </div>
            </section>
            <section class="span3 section-screenings well">
                <div class="page-header" style="margin-top:0">
                    <h2>Vorf&uuml;hrungen</h2>
                </div>
                <ul>
<?php
    foreach ($itemData['screenings'] as $theaterName => $theaterScreenings)
    {
        $theaterTicket = $theaterScreenings['theater']['ticket'];
        $editTheaterLink = $ro->gen('workflow.run', array('ticket' => $theaterTicket, 'type' => 'shofi'));
?>
                    <li>
                        <h4><a href="<?php echo $editTheaterLink; ?>"><?php echo $theaterName; ?></a></h4>
<?php
        foreach ($theaterScreenings['screenings'] as $date => $screeningsOnDate)
        {
?>
                        <h5><?php echo $date; ?></h5>
                        <ul>
<?php
            foreach ($screeningsOnDate as $screening)
            {
?>
                            <li>
<?php 
    $text = implode(', ', $screening['times']);
    if (isset($screening['version']))
    {
        $text .= ', Fassung: ' . $screening['version'];
    }
    echo $text;
?>
                            </li>
<?php
            }
?>
                        </ul>
<?php
        }
?>
                        <hr />
                    </li>
<?php
    }
?>
                </ul>
            </section>
            <section class="span2 section-media well">
                <div class="page-header" style="margin-top:0">
                    <h2>Medien</h2>
                </div>
                <div>
                    <h4>Trailer</h4>
                    <ul>
<?php
    foreach ($itemData['media']['trailers'] as $trailerName => $trailer)
    {
?>
                        <li>
                            <a target="_blank" href="<?php echo $trailer; ?>"><?php echo $trailerName ?></a>
                        </li>
<?php
    }
?>
                    </ul>
                    <h4>Bilder</h4>
                    <ul class="movie-images-list">
<?php
    foreach ($itemData['media']['images'] as $name =>$image)
    {
?>
                        <li>
                            <h5 class="label label-info"><?php echo $name; ?></h5>
                            <p><?php echo sprintf("%s (%sx%s)", $image['filename'], $image['width'], $image['height']); ?></p>
                            <img src="<?php echo $image['src']; ?>" />
                        </li>
<?php
    }
?>
                    </ul>
<h4>Gallerien</h4>
                    <ul class="movie-galleries-list">
<?php
    foreach ($itemData['media']['galleries'] as $galleryName => $images)
    {
?>
                        <li>
                            <h5 class="label label-info"><?php echo $galleryName; ?></h5>
                            <ul>
<?php
        foreach ($images as $image)
        {
?>
                                <li>
                                    <p><?php echo sprintf("%s (%sx%s)", $image['filename'], $image['width'], $image['height']); ?></p>
                                    <img src="<?php echo $image['src']; ?>" />
                                </li>
<?php
        }
?>
                            </ul>
                        </li>
<?php
    }
?>
                    </ul>
            </section>
        </div>
    </form>
</div>
