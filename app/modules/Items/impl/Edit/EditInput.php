<?php
    $ticketData = $t['ticket'];
    $workflowItem = $ticketData['item'];
    $importItem = $workflowItem['importItem'];
    $contentItems = $workflowItem['contentItems'];
?>
<header>
    <h1>
        <a href="<?php echo $ro->gen('index'); ?>">Midas</a>
    </h1>
    <aside class="personal-info">
        <h2>Userinfo Box</h2>
        <p>
            Diese Nachricht wird von <?php echo htmlspecialchars($t['editor']); ?> bearbeitet.
            Du hast bisher 0 Mails abgehakt.
        </p>
        <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
    </aside>
</header>
<!-- Holds the routing data for the edit view -->
<div class="jsb-routing">
    <input class="jsb-routing-options" value="<?php echo htmlspecialchars(json_encode($t['edit_view_routes'])); ?>" />
</div>
<div class="topmenu-container content-menu">
    <menu id="content-item-menu" class="action-button-list">
        <li>
            <a class="action-list btn small" href="#list">Liste</a>
            <span class="info-small">&#160;</span>
        </li>
        <li><a class="action-store btn small important" href="#store">Speichern</a></li>
        <li><a class="action-new btn small" href="#new">Neues Item</a></li>
        <li><a class="action-delete btn small danger" href="#delete">L&#246;schen</a></li>
    </menu>
</div>
<div class="topmenu-container import-menu">
    <menu id="import-item-menu" class="action-button-list">
        <li><a href="#prev" class="btn small">&#8592; Zur&#252;ck</a></li>
        <li><a href="#mark" class="btn small important">Abhaken</a></li>
        <li><a href="#next" class="btn small">Vor &#8594;</a></li>
        <li><a href="#delete" class="btn small danger">L&#246;schen</a></li>
    </menu>
</div>
<div class="slide-panel">
    <section class="document-editing">
        <h2>Document editing form</h2>

        <section class="content-items">
            <input type="hidden" class="content-list-src" value="<?php echo htmlspecialchars(json_encode($contentItems)); ?>" />
            <div class="content-panel">
                <h3 class="legend">Items</h3>
                <ul></ul>
            </div>
        </section>

        <form accept-charset="utf-8" action="#postdata" method="post">
            <input class="static-value release-ticket-base-url" type="hidden" value="<?php echo urldecode($t['release_url']); ?>" />
            <input class="static-value list-filter" type="hidden" value="<?php echo htmlspecialchars(json_encode($t['list_filter'])); ?>" />
            <input class="static-value grab-ticket-base-url" type="hidden" value="<?php echo urldecode($t['grab_url']); ?>" />
            <input class="static-value list-base-url" type="hidden" value="<?php echo urldecode($t['list_url']); ?>" />
            <input type="hidden" class="static-value list_position" value="<?php echo $t['list_pos']; ?>" />

            <input type="hidden" class="static-value ticket-identifier" name="ticket" value="<?php echo $ticketData['_id']; ?>" />
            <input type="hidden" class="static-value workflow-item-identifier" name="parentIdentifier" value="<?php echo $workflowItem['identifier']; ?>" />
            <input type="hidden" name="cid" value="0" />
            <input type="hidden" name="identifier" value="0" />

            <div class="main-data content-panel"> <!-- <fieldset> as soon as the firefox (legend position render) bug is fixed -->
                <h3 class="legend">Redaktionelle Einstellungen</h3>
                <div class="input-left category">
                    <label for="input_category">Kategorie</label>
                    <select id="input_category" class="jsb-input" name="category">
                        <option value=""></option>
<?php
    foreach ($t['category_options'] as $category)
    {
?>
                        <option value="<?php echo strtolower($category); ?>"><?php echo $category; ?></option>
<?php
    }
?>
                    </select>
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-left priority">
                    <label for="input_priority" for="Priorität">Priorität</label>
                    <select id="input_priority" class="jsb-input" name="priority">
                        <option value=""></option>
                        <option value="1">niedrig</option>
                        <option value="2">mittel</option>
                        <option value="3">hoch</option>
                    </select>
                    <input type="hidden" value='{ "min": 1, "max": 3, "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-left editor">
                    <label label="input_publisher" for="publisher">Bearbeiter:</label>
                    <input class="static-value" id="input_publisher" name="publisher" type="text" readonly="readonly" value="<?php echo htmlspecialchars($t['editor']); ?>" />
                </div>
                <div class="input-full tags">
                    <label for="input_tags">Tags</label>
                    <ul class="tagHandlerContainer">
                        <li class="tagInput">
                            <input class="jsb-input-tag tagInputField ui-autocomplete-input" name="tags" type="text" id="input_tags" />
                            <input type="hidden" value="<?php echo htmlspecialchars(json_encode($t['tag_options'])); ?>" class="jsb-input-tag-options" />
                        </li>
                    </ul>
                </div>
                <div class="input-full title">
                    <label for="input_title" for="title">Titel</label>
                    <input id="input_title" class="jsb-input" name="title" type="text" />
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-full">
                    <label for="input_teaser">
                        Teaser. Die ersten drei Sätze Deines Texts, die Du selber schreiben kannst.
                    </label>
                    <textarea id="input_teaser" name="teaser" cols="2" rows="6"></textarea>
                </div>
                <div class="input-full">
                    <label for="input_text">
                        <strong>Text</strong>. Der Rest des Texts. Hier sollst Du vor allem kürzen.
                    </label>
                    <textarea id="input_text" class="jsb-input-assistive-text" name="text" rows="10" cols="30"></textarea>
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-assistive-text-options" />
                </div>
                <div class="input-full">
                    <label for="input_source">Quelle (Wer hat das geschickt. Z.B.: Bezirksamt Marzahn-Hellersdorf. Unbedingt ausfüllen.)</label>
                    <input id="input_source" class="jsb-input" name="source" type="text" />
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-full">
                    <label for="input_url">URL (Verknüpfte Internetadresse)</label>
                    <input id="input_url" class="jsb-input-url" name="url" type="text" />
                    <input type="hidden" value='{ "mandatory": false }' class="jsb-input-url-options" />
                </div>
            </div> <!-- </fieldset> as soon as the firefox render bug is fixed -->

            <div class="extra-data-left">
                <div class="geo-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Geo <img class="localize-icon" src="images/icon-reload.png" title="Localize" /></h3>
                    <div class="input-full">
                        <select class="jsb-input" name="location[relevance]">
                            <option value=""></option>
                            <option value="0">Betrifft den Bezirk (z.B. Wilmersdorf)</option>
                            <option value="1">Betrifft den Verwaltungsbezirk (z.B. Charlottenburg-Wilmersdorf)</option>
                            <option value="2">Betrifft die ganze Stadt</option>
                        </select>
                        <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_name">Name des Orts (z.B: KaDeWe)</label>
                        <input id="input_location_name" name="location[name]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_locationdetail">Zusätzliche Ortsangabe (z.B.: Haus 3)</label>
                        <input id="input_location_locationdetail" name="location[locationdetail]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_street">Straße, Hausnummer</label>
                        <input id="input_location_street" name="location[street]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_uzip">PLZ</label>
                        <input id="input_location_uzip" name="location[postalCode]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_neighborhood">Bezirk</label>
                        <input id="input_location_neighborhood" name="location[administrativeDistrict]" type="text" readonly="readonly" />
                    </div>
                    <div class="input-full">
                        <label for="input_location_subneighborhood">Alter Bezirksname</label>
                        <input id="input_location_subneighborhood" name="location[district]" type="text" readonly="readonly" />
                    </div>
                    <input type="hidden" name="location[coordinates][lon]" value="0" />
                    <input type="hidden" name="location[coordinates][lat]" value="0" />
                    <div id="geo-busy-overlay"></div>
                </div> <!-- </fieldset> -->
            </div>

            <div class="extra-data-right">
                <div class="datetime-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Zeiten</h3>
                    <div class="item-isevent input-full">
                        <label for="input_date_isevent">Ist Teilnahme des Nutzers durch den Veranstalter erwünscht?</label>
                        <input id="input_date_isevent" type="checkbox" name="date[isevent]" value="1" />
                    </div>
                    <div class="input-full">
                        <label for="input_date_from">
                            Wann findet das statt? <em class="bold">Bei Ausstellungen: Startdatum = Enddatum</em>
                            von
                        </label>
                        <input id="input_date_from" name="date[from]" class="jsb-input-date" type="text" />
                        <input type="hidden" value='{ "regex": "^[0-9]{1,2}\\.[0-9]{1,2}\\.[0-9]{4,4}$", "date_format": "dd.mm.yy" }' class="jsb-input-date-options" />
                    </div>
                    <div class="input-full">
                        <label for="input_date_till">bis</label>
                        <input id="input_date_till" name="date[till]" class="jsb-input-date" type="text" />
                        <input type="hidden" value='{  "regex": "^[0-9]{1,2}\\.[0-9]{1,2}\\.[0-9]{4,4}$", "date_format": "dd.mm.yy" }' class="jsb-input-date-options" />
                    </div>
                </div> <!-- </fieldset> -->

                <section class="nearby-items content-panel">
                    <h3 class="legend">Items in der Nähe</h3>
                    <ul>
                        <li>
                            <!-- <a href="/items/edit/{{Item.parent}}">
                                ({{Item.location.neighborhood|truncate 100}}) {{Item.title|truncate 80}}
                            </a> -->
                        </li>
                    </ul>
                </section>
            </div>
        </form>
    </section>

    <section class="document-data">
        <h2>Current Import Item Data</h2>
        <div class="import-data-layoutbox">
            <div class="item-meta-data content-panel">
                <h3 class="legend">Daten</h3>
                <dl>
                    <dt>Betreff</dt>
                    <dd class="subject"><?php echo $importItem['title']; ?></dd>
                    <dt>Von</dt>
                    <dd class="source"><?php echo $importItem['source']; ?></dd>
                    <dt>Versanddatum</dt>
                    <dd class="timestamp"><?php echo $importItem['timestamp']; ?></dd>
                </dl>
            </div>

            <div class="item-content content-panel">
                <h3 class="legend">Verf&#252;gbare Daten</h3>
                <ul>
                    <li>
                        <a href="#content-tabs-1">Inhalt</a>
                    </li>
                </ul>
                <div id="content-tabs-1">
                    <div class="input-full">
                        <textarea class="text-content" readonly="readonly"><?php echo strip_tags(htmlspecialchars_decode($importItem['content'])); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div id="edit-gui-busy-overlay"></div>
<!-- Template for rendering form validation error hints. -->
<script id="input-error-tpl" type="text/html">
    <div class="error-hint ui-corner-all bubble-content">
        <div class="error-hint-body">
            <ul>
                {{#messages}}
                <li>
                    <h4>{{ topic }}:</h4>
                    <p>{{ message }}</p>
                </li>
                {{/messages}}
            </ul>
            <div class="bubble-hook"></div>
        </div>
    </div>
</script>
<!-- Template for rendering confirm and warn dialogs. -->
<script id="dialog-tpl" type="text/html">
    <div title="{{ title }}">
        <p>{{ message }}</p>
    </div>
</script>
<!-- Template for rendering content-item list items -->
<script id="content-item-tpl" type="text/html">
    <li class="content-item">
        <article>
            <hgroup>
                <h3>{{ title }}<span class="date">{{ date[from] }}</span></h3>
            </hgroup>
            <p>{{ text }}</p>
        </article>
    </li>
</script>
