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
                <legend>Комментарии</legend>
                {if $sError}
                    <div class="alert alert-danger">
                        {$sError}
                    </div>
                {elseif $nDone}
                    <input type="hidden" name="done" value="{$nDone}">
                    <p>Перенесено комментариев: {$nCommentsCnt}</p>
                {else}
                    <label>Таблица 1</label>
                    <input type="text" name="jcomments" value="{$aData.jcomments}">
                    <br/>
                {/if}
            </fieldset>
            {if !$sError}
                <button class="btn btn-primary pull-right">Дальше</button>
            {/if}
        </form>
    </article>
{/block}
