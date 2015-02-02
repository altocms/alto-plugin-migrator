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
                <legend>Пользователи</legend>
                {if $sError}
                    <div class="alert alert-danger">
                        {$sError}
                    </div>
                {elseif $aErrUsers}
                    Ошибка! Данные пользователей для миграции совпадают с пользователями в базе данных:<br/><br/>
                    <ul>
                        {foreach $aErrUsers as $aUser}
                            <li>
                                user_id: {if $aUser.id==$aUser.user_id}<strong>{/if}{$aUser.user_id}{if $aUser.id==$aUser.user_id}</strong>{/if},
                                user_login: {if $aUser.username==$aUser.user_login}<strong>{/if}{$aUser.user_login}{if $aUser.username==$aUser.user_login}</strong>{/if},
                                user_mail: {if $aUser.email==$aUser.user_mail}<strong>{/if}{$aUser.user_mail}{if $aUser.email==$aUser.user_mail}</strong>{/if}
                            </li>
                        {/foreach}
                    </ul>
                    <p>
                    Исправьте данные в таблице "{Config::Get('db.table.prefix')}user" и продолжите миграцию.
                    </p>
                {elseif $nDone}
                    <input type="hidden" name="done" value="{$nDone}">
                    <p>Перенесено пользователей: {$nUsersCnt}</p>
                    {if $aUsersChanged}
                        <p>У следующих пользователей был изменен логин:</p>
                        <ul>
                            {foreach $aUsersChanged as $nUserId=>$aUser}
                                <li>
                                    {$nUserId}:
                                    {$aUser.old_login} -&gt;
                                    {if $aUser.transform}<strong>{/if}{$aUser.new_login}{if $aUser.transform}</strong>{/if}
                                </li>
                            {/foreach}
                        </ul>
                    {/if}
                {else}
                    <label>Таблица 1</label>
                    <input type="text" name="jusers1" value="{$aData.jusers1}">
                    <br/>
                    <label>Таблица 2</label>
                    <input type="text" name="jusers2" value="{$aData.jusers2}">
                    <br/>
                    <label>Таблица 3</label>
                    <input type="text" name="jusers3" value="{$aData.jusers3}">
                    <br/>
                {/if}
            </fieldset>
            {if !$sError}
                <button class="btn btn-primary pull-right">Дальше</button>
            {/if}
        </form>
    </article>
{/block}
