{extends file="_index.tpl"}

{block name="layout_vars"}
{/block}

{block name="layout_content"}
    <article class="topic js-topic">
        <h3>Миграция данных - Joomla 1.5.x - Step {$nStep}</h3>

        <form method="post" action="">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="step" value="{$nStep}"/>
            {$aData=Config::Get($sKey)}
            <fieldset>
                <legend>Топики</legend>
                {if $sError}
                    <div class="alert alert-danger">
                        {$sError}
                    </div>
                {elseif $nDone}
                    <input type="hidden" name="done" value="{$nDone}">
                    <p>Перенесено топиков: {$nTopicsCnt}</p>
                {else}
                    <label>Таблица 1</label>
                    <input type="text" name="jtopics" value="{$aData.jtopics}">
                    <br/>
                {/if}
            </fieldset>
            {if !$sError}
                <button class="btn btn-primary pull-right">Дальше</button>
            {/if}
        </form>
    </article>
{/block}
