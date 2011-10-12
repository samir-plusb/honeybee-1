<!-- ######################## document header ######################## -->
<header>
    <h1>
        <a href="#"><?php echo $t['_title']; ?></a>
    </h1>
    <section id="completed">
        <h2>Userinfo Box</h2>
        <p>
            Diese Nachricht wird von <a href="/items/reportPerson/"></a> bearbeitet.
            Du hast bisher 0 Mails abgehakt.
        </p>
    </section>
</header>

<!-- ######################## document navigation ######################## -->
<nav class="itemControls">
    <h2>Main Navigation</h2>
    <ul>
        <li class="button">
            <a href="" id="action_list">Liste</a>
            <span id="itemscounter">0</span>
        </li>
        <li class="button red"><a href="" id="action_save">Speichern</a></li>
        <li class="button red"><a href="" id="action_new">Neues Item</a></li>
        <li class="button"><a href="" id="action_delete">Löschen</a></li>
        <li class="button right"></li>
        <li class="button">
            <a href="#" id="action_complete">Abhaken</a>
        </li>
        <li class="button"></li>
        <li class="button"></li>
    </ul>
</nav>

<!-- ######################## document editing ######################## -->
<section id="content" class="items panel2">
    <h2 class="legend">Redaktionelle Einstellungen</h2>
    <form id="itemsEditForm" accept-charset="utf-8" action="/items/edit/" method="post">
        <input type="hidden" name="data[status]" id="status" />

        <fieldset>
            <legend class="legend">Hauptdaten</legend>
            <div class="vertical">
                <label for="Kategorie">Kategorie</label>
                <select name="data[category]" id="category">
                    <option value=""></option>
                    <option value="Polizeimeldungen">Polizeimeldungen</option>
                    <option value="Kiezleben">Kiezleben</option>
                    <option value="Kiezkultur">Kiezkultur</option>
                    <option value="Stadtteilentwicklung">Stadtteilentwicklung</option>
                    <option value="Bekanntmachung">Bekanntmachung</option>
                </select>
            </div>
            <div class="vertical">
                <label for="Priorität">Priorität</label>
                <select name="data[priority]" id="priority">
                    <option value=""></option>
                    <option value="0">niedrig</option>
                    <option value="1" selected="selected">mittel</option>
                    <option value="2">hoch</option>
                </select>
            </div>
            <div class="vertical">
                <label for="username">Bearbeiter:</label>
                <input name="data[username]" type="text" disabled="disabled" class="username" id="username" />
            </div>
            <div class="input text title_box">
                <label for="title">Titel</label>
                <input name="data[title]" type="text" id="title" />
            </div>
            <div class="input textarea">
                <label for="teaser">
                    Teaser. Die ersten drei Sätze Deines Texts, die Du selber schreiben kannst.
                </label>
                <textarea name="data[teaser]" cols="2" rows="6" id="teaser" ></textarea>
            </div>
            <div class="input textarea">
                <label for="text">
                    <strong>Text</strong>. Der Rest des Texts. Hier sollst Du vor allem kürzen.
                </label>
                <textarea name="data[text]" rows="10" cols="30" id="text" ></textarea>
            </div>
            <div class="input text">
                <label for="source">Quelle (Wer hat das geschickt. Z.B.: Bezirksamt Marzahn-Hellersdorf. Unbedingt ausfüllen.)</label>
                <input name="data[source]" type="text" id="source" />
            </div>
            <div class="input text">
                <label for="url">URL (Verknüpfte Internetadresse)</label>
                <input name="data[url]" type="text" id="url" />
            </div>
        </fieldset>

        <fieldset class="half1">
            <legend class="legend">Geo</legend>
            <input type="hidden" name="data[location[longitude]]" id="location[longitude]" />
            <input type="hidden" name="data[location[latitude]]" id="location[latitude]" />
            <div class="input text">
                <select name="data[location[relevance]]" id="location[relevance]">
                    <option value="" selected="selected"></option>
                    <option value="0" selected="selected">Betrifft den Bezirk (z.B. Wilmersdorf)</option>
                    <option value="1">Betrifft den Verwaltungsbezirk (z.B. Charlottenburg-Wilmersdorf)</option>
                    <option value="2">Betrifft die ganze Stadt</option>
                </select>
            </div>
            <div class="input text">
                <label for="location[name]">Name des Orts (z.B: KaDeWe)</label>
                <input name="data[location[name]]" type="text" id="location[name]" />
            </div>
            <div class="input text"><label for="location[locationdetail]">Zusätzliche Ortsangabe (z.B.: Haus 3)</label>
                <input name="data[location[locationdetail]]" type="text" id="location[locationdetail]" />
            </div>
            <div class="input text">
                <label for="location[street]">Straße, Hausnummer</label>
                <input name="data[location[street]]" type="text" id="location[street]" />
            </div>
            <div class="input text">
                <label for="location[uzip]">PLZ</label>
                <input name="data[location[uzip]]" type="text" id="location[uzip]" />
            </div>
            <div class="input text">
                <label for="location[neighborhood]">Bezirk</label>
                <input name="data[location[neighborhood]]" type="text" disabled="disabled" id="location[neighborhood]" />
            </div>
            <div class="input text">
                <label for="location[subneighborhood]">Alter Bezirksname</label>
                <input name="data[location[subneighborhood]]" type="text" disabled="disabled" id="location[subneighborhood]" />
            </div>
        </fieldset>

        <fieldset class="half2">
            <legend class="legend">Zeiten</legend>
            <div class="input checkbox">
                <label for="date[isevent]">Ist Teilnahme des Nutzers durch den Veranstalter erwünscht?</label>
                <input type="hidden" name="data[date[isevent]]" id="date[isevent]_" value="0" />
                <input type="checkbox" name="data[date[isevent]]" value="1" id="date[isevent]" />
            </div>
            <div class="input text">
                <label for="date[from]">Wann findet das statt? <strong>Bei Ausstellungen: Startdatum = Enddatum</strong>. von</label>
                <input name="data[date[from]]" type="text" id="date[from]" />
            </div>
            <div class="input text">
                <label for="date[till]">bis</label>
                <input name="data[date[till]]" type="text" id="date[till]" />
            </div>
        </fieldset>

        <section class="half2">
            <h3 class="legend">Items in der Nähe</h3>
            <ul id="nearbylist">
                <li data-template="" class="nearbyitem">
                    <a style="text-decoration:none; font-weight:normal" href="/items/edit/{{Item.parent}}">
                        ({{Item.location.neighborhood|truncate 100}}) {{Item.title|truncate 80}}
                    </a>
                </li>
            </ul>
        </section>
    </form>
</section>

<!-- ######################## available import items pool ######################## -->
<section class="panel1">
    <h2 class="legend">Items</h2>
    <ul id="itembox">
        <li data-template="" class="newsitem">
            <article>
                <hgroup>
                    <h3>{{title|truncate 45}}</h3>
                </hgroup>
                <p class="date">{{date.from}}</p>
                <p>{{text|truncate 100}}</p>
            </article>
        </li>
    </ul>
</section>

<!-- ######################## selected import item - meta data ######################## -->
<section class="panel3">
    <h2 class="legend">Meta Daten</h2>
    <dl class="fieldsetInner">
        <dt>Betreff</dt>
        <dd></dd>
        <dt>Von</dt>
        <dd></dd>
        <dt>Versanddatum</dt>
        <dd>01:00 - 01.01.1970</dd>
    </dl>
</section>

<!-- ######################## selected import item - content data ######################## -->
<section class="panel3">
    <h2 class="legend">Content</h2>
    <ul id="tabs">
        <li>
            <a href="#tabs-1">Inhalt</a>
        </li>
    </ul>
    <div id="tabs-1">
        <textarea id="textinner" readonly="readonly" class="textinner"></textarea>
    </div>
</section>

<!-- ######################## document footer ######################## -->
<footer id="footer"></footer>