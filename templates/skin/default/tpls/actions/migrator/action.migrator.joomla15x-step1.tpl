{extends file="_index.tpl"}

{block name="layout_content"}
<article class="topic js-topic">
    <h3>Миграция данных - Joomla 1.5.x - Step {$nStep}</h3>
    <form method="post" action="">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />
        <input type="hidden" name="step" value="{$nStep}" />
        {$aData=Config::Get($sKey)}
        <p>
            До начала миграции убедитесь, что таблицы исходной базы данных
            добавлены в текущую базу данных.
        </p>
        <p>
            Текущая база данных: <strong>{Config::Get('db.params.dbname')}</strong>
        </p>
        <div class="alert alert-danger">
            Обязательно сделайте копию базы данных прежде, чем продолжите миграцию!
        </div>
        <p>
            Префикс таблиц Joomla: <input type="text" name="jprefix" value="{$aData.jprefix}" />
        </p>
        <button class="btn btn-primary pull-right">Дальше</button>
    </form>
</article>
{/block}
