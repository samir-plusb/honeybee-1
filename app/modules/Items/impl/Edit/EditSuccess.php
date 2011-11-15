<header>
    <h1>
        <a href="#home"><?php echo $t['_title']; ?></a>
    </h1>
    <aside class="personal-info">
        <h2>Userinfo Box</h2>
        <p>
            Diese Nachricht wird von <a href="#profile?"></a> bearbeitet.
            Du hast bisher 0 Mails abgehakt.
        </p>
    </aside>
</header>

<div class="slide-panel">
    <section class="document-editing">
        <h2>Document editing form</h2>
        <menu id="content-item-menu" class="action-button-list">
            <li>
                <a class="action-list" href="#list">Liste</a>
                <span class="info-small items-loading">&nbsp;&nbsp;</span>
            </li>
            <li><a class="action-store" href="#store">Speichern</a></li>
            <li><a class="action-new" href="#new">Neues Item</a></li>
            <li><a class="action-delete" href="#delete">L&ouml;schen</a></li>
        </menu>

        <section class="content-items">
            <div class="content-panel">
                <h3 class="legend">Items</h3>
                <ul></ul>
            </div>
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
        </section>

        <form accept-charset="utf-8" action="#postdata" method="post">
            <input type="hidden" name="status" id="status" />
            <input type="hidden" name="cid" value="0" />

            <div class="main-data content-panel"> <!-- <fieldset> as soon as the firefox (legend position render) bug is fixed -->
                <h3 class="legend">Redaktionelle Einstellungen</h3>
                <div class="input-left category">
                    <label for="Kategorie">Kategorie</label>
                    <select class="jsb-input" name="category">
                        <option value=""></option>
                        <option value="Polizeimeldungen">Polizeimeldungen</option>
                        <option value="Kiezleben">Kiezleben</option>
                        <option value="Kiezkultur">Kiezkultur</option>
                        <option value="Stadtteilentwicklung">Stadtteilentwicklung</option>
                        <option value="Bekanntmachung">Bekanntmachung</option>
                    </select>
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-left priority">
                    <label for="Priorität">Priorität</label>
                    <select class="jsb-input" name="priority">
                        <option value=""></option>
                        <option value="1">niedrig</option>
                        <option value="2">mittel</option>
                        <option value="3">hoch</option>
                    </select>
                    <input type="hidden" value='{ "min": 1, "max": 3, "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-left editor">
                    <label for="username">Bearbeiter:</label>
                    <input name="username" type="text" readonly="readonly" />
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
                    <label for="title">Titel</label>
                    <input class="jsb-input" name="title" type="text" />
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-full">
                    <label for="teaser">
                        Teaser. Die ersten drei Sätze Deines Texts, die Du selber schreiben kannst.
                    </label>
                    <textarea name="teaser" cols="2" rows="6"></textarea>
                </div>
                <div class="input-full">
                    <label for="text">
                        <strong>Text</strong>. Der Rest des Texts. Hier sollst Du vor allem kürzen.
                    </label>
                    <textarea class="jsb-input-assistive-text" name="text" rows="10" cols="30"></textarea>
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-assistive-text-options" />
                </div>
                <div class="input-full">
                    <label for="source">Quelle (Wer hat das geschickt. Z.B.: Bezirksamt Marzahn-Hellersdorf. Unbedingt ausfüllen.)</label>
                    <input class="jsb-input" name="source" type="text" />
                    <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-full">
                    <label for="url">URL (Verknüpfte Internetadresse)</label>
                    <input class="jsb-input-url" name="url" type="text" />
                    <input type="hidden" value='{ "mandatory": false }' class="jsb-input-url-options" />
                </div>
            </div> <!-- </fieldset> as soon as the firefox render bug is fixed -->

            <div class="extra-data-left">
                <div class="geo-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Geo</h3>
                    <input type="hidden" name="location[longitude]" id="location[longitude" />
                    <input type="hidden" name="location[latitude]" id="location[latitude" />
                    <div class="input-full">
                        <select class="jsb-input" name="location[relevance]" id="location[relevance">
                            <option value=""></option>
                            <option value="0">Betrifft den Bezirk (z.B. Wilmersdorf)</option>
                            <option value="1">Betrifft den Verwaltungsbezirk (z.B. Charlottenburg-Wilmersdorf)</option>
                            <option value="2">Betrifft die ganze Stadt</option>
                        </select>
                        <input type="hidden" value='{ "mandatory": true }' class="jsb-input-options" />
                    </div>
                    <div class="input-full">
                        <label for="location[name">Name des Orts (z.B: KaDeWe)</label>
                        <input name="location[name]" type="text" />
                    </div>
                    <div class="input-full"
                         ><label for="location[locationdetail">Zusätzliche Ortsangabe (z.B.: Haus 3)</label>
                        <input name="location[locationdetail]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[street]">Straße, Hausnummer</label>
                        <input name="location[street]]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[uzip]">PLZ</label>
                        <input name="location[uzip]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[neighborhood]">Bezirk</label>
                        <input name="location[neighborhood]" type="text" readonly="readonly" />
                    </div>
                    <div class="input-full">
                        <label for="location[subneighborhood]">Alter Bezirksname</label>
                        <input name="location[subneighborhood]" type="text" readonly="readonly" />
                    </div>
                </div> <!-- </fieldset> -->
            </div>

            <div class="extra-data-right">
                <div class="datetime-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Zeiten</h3>
                    <div class="input-full">
                        <label for="date[isevent]">Ist Teilnahme des Nutzers durch den Veranstalter erwünscht?</label>
                        <input type="hidden" name="date[isevent" value="0" />
                        <input type="checkbox" name="date[isevent" value="1" />
                    </div>
                    <div class="input-full">
                        <label for="date[from]">
                            Wann findet das statt? <span>Bei Ausstellungen: Startdatum = Enddatum</span>. von
                        </label>
                        <input name="date[from]" class="jsb-input-date" type="text" />
                        <input type="hidden" value='{ "regex": "^[0-9]{1,2}\\.[0-9]{1,2}\\.[0-9]{4,4}$", "date_format": "dd.mm.yy" }' class="jsb-input-date-options" />
                    </div>
                    <div class="input-full">
                        <label for="date[till]">bis</label>
                        <input name="date[till]" class="jsb-input-date" type="text" />
                        <input type="hidden" value='{  "regex": "^[0-9]{1,2}\\.[0-9]{1,2}\\.[0-9]{4,4}$", "date_format": "dd.mm.yy" }' class="jsb-input-date-options" />
                    </div>
                </div> <!-- </fieldset> -->

                <section class="nearby-items content-panel">
                    <h3 class="legend">Items in der Nähe</h3>
                    <ul>
                        <li data-template="">
                            <a href="/items/edit/{{Item.parent}}">
                                ({{Item.location.neighborhood|truncate 100}}) {{Item.title|truncate 80}}
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
            <!-- Template for rendering form validation error hints. -->
            <script id="input-error-tpl" type="text/html">
                <div class="error-hint ui-corner-all">
                    <div class="error-hint-body">
                        <ul>
                            {{#messages}}
                            <li>
                                <h4>{{ topic }}:</h4>
                                <p>{{ message }}</p>
                            </li>
                            {{/messages}}
                        </ul>
                    </div>
                </div>
            </script>
        </form>
        <!-- Template for rendering confirm and warn dialogs. -->
        <script id="dialog-tpl" type="text/html">
            <div title="{{ title }}">
                <p>{{ message }}</p>
            </div>
        </script>
    </section>

    <section class="document-data">
        <h2>Current Import Item Data</h2>
        <menu id="import-item-menu" class="action-button-list">
            <li><a href="#prev">Zur&uuml;ck</a></li>
            <li><a href="#mark">Abhaken</a></li>
            <li><a href="#delete">L&ouml;schen</a></li>
            <li><a href="#next">Vor</a></li>
        </menu>

        <div class="item-meta-data content-panel">
            <h3 class="legend">Daten</h3>
            <dl>
                <dt>Betreff</dt>
                <dd></dd>
                <dt>Von</dt>
                <dd></dd>
                <dt>Versanddatum</dt>
                <dd>01:00 - 01.01.1970</dd>
            </dl>
        </div>

        <div class="item-content content-panel">
            <h3 class="legend">Verf&uuml;gbare Daten</h3>
            <ul>
                <li>
                    <a href="#content-tabs-1">Inhalt</a>
                </li>
            </ul>
            <div id="content-tabs-1">
                <div class="input-full">
                    <textarea readonly="readonly">Lorem ipsum -dolor sit amet-,
consectetur adipisc--ing elit.
Aliquam -id elit at - www.heise.de
libero t-incidunt luctus id:
http://www.google.de
quis nulla. Praesent molestie porttitor ultricies.
Etiam viverra tempor magna, eget rutru
12 jan 1987
m erat volutpat non. Aliquam vitae eros urna. Etiam viverra porttitor urna, in blandit purus tincidunt non.
Donec a adipiscing magna. Etiam consectetur blandit diam, et pretium tellus congue ut.</textarea>
                </div>
            </div>
        </div>
    </section>
</div>