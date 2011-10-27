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
                <a href="#list">Liste</a>
                <span class="info-small">1</span>
            </li>
            <li class="inactive"><a href="#store">Speichern</a></li>
            <li><a href="#new">Neues Item</a></li>
            <li><a href="#delete">L&ouml;schen</a></li>
        </menu>

        <section class="content-items">
            <div class="content-panel">
                <h3 class="legend">Items</h3>
                <ul>
                    <li data-template="">
                        <article>
                            <hgroup>
                                <h3>{{title|truncate 45}}</h3>
                            </hgroup>
                            <p>{{date.from}}</p>
                            <p>{{text|truncate 100}}</p>
                        </article>
                    </li>
                </ul>
            </div>
        </section>

        <form accept-charset="utf-8" action="#postdata" method="post">
            <input type="hidden" name="data[status]" id="status" />

            <div class="main-data content-panel"> <!-- <fieldset> as soon as the firefox (legend position render) bug is fixed -->
                <h3 class="legend">Redaktionelle Einstellungen</h3>
                <div class="input-left category">
                    <label for="Kategorie">Kategorie</label>
                    <select name="data[category]">
                        <option value=""></option>
                        <option value="Polizeimeldungen">Polizeimeldungen</option>
                        <option value="Kiezleben">Kiezleben</option>
                        <option value="Kiezkultur">Kiezkultur</option>
                        <option value="Stadtteilentwicklung">Stadtteilentwicklung</option>
                        <option value="Bekanntmachung">Bekanntmachung</option>
                    </select>
                </div>
                <div class="input-left priority">
                    <label for="Priorität">Priorität</label>
                    <select name="data[priority]">
                        <option value=""></option>
                        <option value="0">niedrig</option>
                        <option value="1" selected="selected">mittel</option>
                        <option value="2">hoch</option>
                    </select>
                </div>
                <div class="input-left editor">
                    <label for="username">Bearbeiter:</label>
                    <input name="data[username]" type="text" readonly="readonly" />
                </div>
                <div class="input-full title">
                    <label for="title">Titel</label>
                    <input class="jsb-input" name="data[title]" type="text" />
                    <input type="hidden" value='{ "min_length": 10, "mandatory": true }' class="jsb-input-options" />
                </div>
                <div class="input-full">
                    <label for="teaser">
                        Teaser. Die ersten drei Sätze Deines Texts, die Du selber schreiben kannst.
                    </label>
                    <textarea class="jsb-input" name="data[teaser]" cols="2" rows="6"></textarea>
                </div>
                <div class="input-full">
                    <label for="text">
                        <strong>Text</strong>. Der Rest des Texts. Hier sollst Du vor allem kürzen.
                    </label>
                    <textarea class="jsb-input" name="data[text]" rows="10" cols="30"></textarea>
                </div>
                <div class="input-full">
                    <label for="source">Quelle (Wer hat das geschickt. Z.B.: Bezirksamt Marzahn-Hellersdorf. Unbedingt ausfüllen.)</label>
                    <input class="jsb-input" name="data[source]" type="text" />
                </div>
                <div class="input-full">
                    <label for="url">URL (Verknüpfte Internetadresse)</label>
                    <input class="jsb-input" name="data[url]" type="text" />
                </div>
            </div> <!-- </fieldset> as soon as the firefox render bug is fixed -->

            <div class="extra-data-left">
                <div class="geo-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Geo</h3>
                    <input type="hidden" name="data[location[longitude]]" id="location[longitude]" />
                    <input type="hidden" name="data[location[latitude]]" id="location[latitude]" />
                    <div class="input-full">
                        <select class="jsb-input" name="data[location[relevance]]" id="location[relevance]">
                            <option value="" selected="selected"></option>
                            <option value="0" selected="selected">Betrifft den Bezirk (z.B. Wilmersdorf)</option>
                            <option value="1">Betrifft den Verwaltungsbezirk (z.B. Charlottenburg-Wilmersdorf)</option>
                            <option value="2">Betrifft die ganze Stadt</option>
                        </select>
                    </div>
                    <div class="input-full">
                        <label for="location[name]">Name des Orts (z.B: KaDeWe)</label>
                        <input class="jsb-input" name="data[location[name]]" type="text" />
                    </div>
                    <div class="input-full"
                         ><label for="location[locationdetail]">Zusätzliche Ortsangabe (z.B.: Haus 3)</label>
                        <input class="jsb-input" name="data[location[locationdetail]]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[street]">Straße, Hausnummer</label>
                        <input class="jsb-input" name="data[location[street]]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[uzip]">PLZ</label>
                        <input class="jsb-input" name="data[location[uzip]]" type="text" />
                    </div>
                    <div class="input-full">
                        <label for="location[neighborhood]">Bezirk</label>
                        <input name="data[location[neighborhood]]" type="text" readonly="readonly" />
                    </div>
                    <div class="input-full">
                        <label for="location[subneighborhood]">Alter Bezirksname</label>
                        <input name="data[location[subneighborhood]]" type="text" readonly="readonly" />
                    </div>
                </div> <!-- </fieldset> -->
            </div>

            <div class="extra-data-right">
                <div class="datetime-data content-panel"> <!-- <fieldset> -->
                    <h3 class="legend">Zeiten</h3>
                    <div class="input-full">
                        <label for="date[isevent]">Ist Teilnahme des Nutzers durch den Veranstalter erwünscht?</label>
                        <input type="hidden" name="data[date[isevent]]" value="0" />
                        <input class="jsb-input" type="checkbox" name="data[date[isevent]]" value="1" />
                    </div>
                    <div class="input-full">
                        <label for="date[from]">
                            Wann findet das statt? <span>Bei Ausstellungen: Startdatum = Enddatum</span>. von
                        </label>
                        <input name="data[date[from]]" class="date-picker jsb-input" type="text" />
                        <input type="hidden" value='{  "mandatory": true, "regex": "[0-9]{1,2}.[0-9]{1,2}.[0-9]{4}" }' class="jsb-input-options" />
                    </div>
                    <div class="input-full">
                        <label for="date[till]">bis</label>
                        <input name="data[date[till]]" class="date-picker jsb-input" type="text" />
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
        </form>
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
                    <textarea id="textinner" readonly="readonly" class="textinner"></textarea>
                </div>
            </div>
        </div>
    </section>
</div>