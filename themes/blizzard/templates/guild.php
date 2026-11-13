<?php
use App\Core\Guild;
use App\Core\Theme;
use App\Core\Wow;

$g = $d['guild'];
$facKey = 'db.side_' . ($d['faction'] === 'neutral' ? 'both' : $d['faction']);
?>
<section class="guild-page">
    <div class="container">
        <div class="guild-banner">
            <?php Theme::partial('guild_emblem', ['g' => $g, 'large' => true]); ?>
            <div class="guild-banner-text">
                <h1 class="guild-banner-name">&lt;<?= e($g['name']) ?>&gt;</h1>
                <p class="guild-banner-sub">
                    <span class="fac fac-<?= e($d['faction']) ?>"><?= e(t($facKey)) ?></span>
                    <?php if ($realm): ?> &middot; <?= e($realm) ?><?php endif; ?>
                </p>
                <div class="guild-stats">
                    <span class="guild-stat" title="<?= e(t('guild.members_t')) ?>">
                        &#9787; <?= (int) count($d['members']) ?>
                    </span>
                    <span class="guild-stat" title="<?= e(t('guild.bank_t')) ?>">
                        &#x1F4B0; <?= e(Guild::moneyShort((int) $g['BankMoney'])) ?>
                    </span>
                    <span class="guild-stat" title="<?= e(t('guild.created_t')) ?>">
                        &#x1F550; <?= e(gmdate('M j, Y', (int) $g['createdate'])) ?>
                    </span>
                </div>
            </div>
            <div class="guild-ach">
                <span class="guild-ach-label"><?= t('guild.ach_points') ?></span>
                <span class="guild-ach-num"><?= number_format($ach['points']) ?></span>
                <span class="guild-ach-sub">
                    <?= t('guild.ach_sub', ['earned' => $ach['earned'], 'progress' => $ach['progress']]) ?>
                </span>
            </div>
            <?php if ($perks): ?>
                <div class="guild-perks" data-perks-modal role="button" tabindex="0"
                     aria-label="<?= e(t('guild.perks_view_all')) ?>">
                    <span class="guild-perks-label"><?= t('guild.perks') ?></span>
                    <ul class="guild-perks-list">
                        <?php foreach (array_slice($perks, 0, 4) as $p): ?>
                            <li>
                                <?php if ($p['icon']): ?>
                                    <img class="guild-perk-icon" src="<?= e($p['icon']) ?>"
                                         alt="" loading="lazy" onerror="this.remove()">
                                <?php endif; ?>
                                <span class="guild-perk-name"><?= e($p['name'] ?? 'Perk #' . (int) $p['SpellID']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="guild-perks-all is-hidden">
                    <h3 class="guild-perks-all-title"><?= t('guild.perks') ?></h3>
                    <ul>
                        <?php foreach ($perks as $p): ?>
                            <li class="guild-perk-row">
                                <?php if ($p['icon']): ?>
                                    <img class="guild-perk-icon" src="<?= e($p['icon']) ?>"
                                         alt="" loading="lazy" onerror="this.remove()">
                                <?php endif; ?>
                                <div class="guild-perk-txt">
                                    <span class="guild-perk-name"><?= e($p['name'] ?? 'Perk #' . (int) $p['SpellID']) ?></span>
                                    <?php if ($p['descr']): ?>
                                        <span class="guild-perk-desc"><?= e($p['descr']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <?php if (trim((string) $g['motd']) !== ''): ?>
            <p class="guild-motd">
                <span class="guild-motd-label"><?= t('guild.motd_label') ?></span>
                &ldquo;<?= e($g['motd']) ?>&rdquo;
            </p>
        <?php endif; ?>

        <div class="guild-roster">
            <div class="guild-tabs" role="tablist">
                <button type="button" class="guild-tab active" data-tab="#tab-roster" role="tab">
                    <?= t('guild.tab_roster') ?>
                </button>
                <button type="button" class="guild-tab" data-tab="#tab-achievements" role="tab">
                    <?= t('guild.tab_achievements') ?>
                </button>
                <input class="auth-input guild-search" type="text" autocomplete="off"
                       data-filter="#guildRosterBody" placeholder="<?= e(t('guild.search')) ?>">
            </div>

            <div class="guild-tabpanel" id="tab-roster" role="tabpanel">
                <div class="db-table-wrap">
                    <table class="db-table guild-table" data-sortable>
                        <thead>
                            <tr>
                                <th data-key="0"><?= t('guild.col_name') ?></th>
                                <th data-key="1" class="sorted-asc"><?= t('guild.col_rank') ?></th>
                                <th data-key="2"><?= t('guild.col_level') ?></th>
                                <th data-key="3"><?= t('guild.col_class') ?></th>
                                <th data-key="4"><?= t('guild.col_race') ?></th>
                            </tr>
                        </thead>
                        <tbody id="guildRosterBody">
                            <?php foreach ($d['members'] as $m):
                                $portrait = Wow::portraitPath((int) $m['race'], (int) $m['gender']);
                                ?>
                                <tr>
                                    <td data-v="<?= e(strtolower($m['name'])) ?>">
                                        <?php if ($portrait): ?>
                                            <img class="guild-avatar" src="<?= e(asset($portrait)) ?>"
                                                 alt="" loading="lazy" onerror="this.remove()">
                                        <?php endif; ?>
                                        <a class="guild-char" href="<?= url('/armory?char=' . (int) $m['guid']) ?>">
                                            <?= e($m['name']) ?>
                                        </a>
                                        <?php if ($m['online']): ?>
                                            <span class="guild-online" title="<?= e(t('armory.online')) ?>"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-v="<?= (int) $m['rid'] ?>">
                                        <span class="guild-rank<?= (int) $m['rid'] === 0 ? ' guild-rank-gm' : '' ?>">
                                            <?= e($m['rankName']) ?>
                                        </span>
                                    </td>
                                    <td data-v="<?= (int) $m['level'] ?>" class="num"><?= (int) $m['level'] ?></td>
                                    <td data-v="<?= e(strtolower(Wow::className((int) $m['class']))) ?>">
                                        <span class="cls-<?= (int) $m['class'] ?>">
                                            <?= e(Wow::className((int) $m['class'])) ?>
                                        </span>
                                    </td>
                                    <td data-v="<?= e(strtolower(Wow::raceName((int) $m['race']))) ?>">
                                        <?= e(Wow::raceName((int) $m['race'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="guild-tabpanel is-hidden" id="tab-achievements" role="tabpanel">
                <?php if (!$achievements): ?>
                    <p class="muted guild-ach-empty">
                        <?= t('guild.no_achievements') ?>
                        <?php if ($ach['progress']): ?>
                            <br><?= t('guild.in_progress', ['n' => $ach['progress']]) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="db-table-wrap">
                        <table class="db-table guild-table">
                            <thead>
                                <tr>
                                    <th><?= t('guild.col_ach') ?></th>
                                    <th><?= t('guild.col_points') ?></th>
                                    <th><?= t('guild.col_date') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($achievements as $a): ?>
                                    <tr>
                                        <td>
                                            <?php if ($a['icon']): ?>
                                                <img class="guild-avatar" src="<?= e($a['icon']) ?>"
                                                     alt="" loading="lazy" onerror="this.remove()">
                                            <?php endif; ?>
                                            <?= e($a['title']) ?>
                                        </td>
                                        <td class="num"><?= (int) $a['points'] ?></td>
                                        <td class="num"><?= e(gmdate('M j, Y', (int) $a['date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
