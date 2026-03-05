<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$action = $_GET['action'] ?? 'liste';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'supprimer' && $id && $id != $_SESSION['user_id']) {
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    flashMessage('succes', 'Utilisateur supprimé.');
    redirect(SITE_URL . '/admin/utilisateurs.php');
}

if ($action === 'toggle' && $id) {
    $u = $db->query("SELECT statut FROM users WHERE id = $id")->fetch();
    $ns = $u['statut'] === 'actif' ? 'suspendu' : 'actif';
    $db->prepare("UPDATE users SET statut = ? WHERE id = ?")->execute([$ns, $id]);
    flashMessage('succes', 'Statut modifié.');
    redirect(SITE_URL . '/admin/utilisateurs.php');
}

$search = sanitize($_GET['s'] ?? '');
$role   = sanitize($_GET['role'] ?? '');
$where  = [];
$params = [];
if ($search) { $where[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]); }
if ($role)   { $where[] = "role = ?"; $params[] = $role; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT * FROM users $w ORDER BY date_creation DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$page_title = 'Gestion des Utilisateurs';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title">Utilisateurs (<?= count($users) ?>)</div>
    </div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div style="background:<?= $flash['type']==='succes'?'#f0fdf4':'#fef2f2' ?>;border-left:4px solid <?= $flash['type']==='succes'?'var(--green)':'var(--red)' ?>;padding:14px 16px;border-radius:10px;margin-bottom:20px;display:flex;gap:10px;color:<?= $flash['type']==='succes'?'#166534':'#991b1b' ?>">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="GET" style="background:var(--white);border-radius:14px;padding:20px;margin-bottom:20px;border:1px solid #F1F5F9;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:2;min-width:200px">
                <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:6px">Recherche</label>
                <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Nom, email, téléphone..." style="width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;outline:none;font-family:'DM Sans',sans-serif">
            </div>
            <div style="min-width:150px">
                <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:6px">Rôle</label>
                <select name="role" style="width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;outline:none;font-family:'DM Sans',sans-serif">
                    <option value="">Tous les rôles</option>
                    <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
                    <option value="agent" <?= $role==='agent'?'selected':'' ?>>Agents</option>
                    <option value="locataire" <?= $role==='locataire'?'selected':'' ?>>Locataires</option>
                </select>
            </div>
            <button type="submit" class="btn btn-gold btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="?" class="btn btn-dark btn-sm">Réinitialiser</a>
        </form>

        <div class="table-card">
            <table class="data-table">
                <thead><tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Inscription</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun utilisateur trouvé</td></tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-light));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0">
                                    <?= strtoupper(substr($u['prenom'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></div>
                                    <?php if ($u['derniere_connexion']): ?>
                                    <div style="font-size:11px;color:var(--text-muted)">Dernière co. : <?= timeAgo($u['derniere_connexion']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($u['telephone']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($u['ville'] ?? '-') ?></td>
                        <td>
                            <span style="padding:3px 10px;border-radius:50px;font-size:11px;font-weight:700;text-transform:uppercase;
                                background:<?= $u['role']==='admin'?'#fef3c7':($u['role']==='agent'?'#eff6ff':'#f0fdf4') ?>;
                                color:<?= $u['role']==='admin'?'#92400e':($u['role']==='agent'?'var(--blue)':'var(--green)') ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="openToggleModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?>', '<?= $u['statut'] ?>')" style="background:none;border:none;cursor:pointer;padding:0">
                                <span class="status-badge status-<?= $u['statut'] ?>"><?= $u['statut'] ?></span>
                            </button>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= date('d/m/Y', strtotime($u['date_creation'])) ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <button onclick="openDeleteModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?>')"
                               style="padding:5px 9px;border-radius:7px;background:#fef2f2;color:var(--red);font-size:12px;border:none;cursor:pointer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <span style="font-size:12px;color:var(--text-muted)">Vous</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL SUPPRESSION -->
<div id="modal-delete" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="closeModal('modal-delete')"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-trash" style="font-size:24px;color:var(--red)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Supprimer l'utilisateur</h3>
            <p style="color:var(--text-muted);font-size:14px">Vous allez supprimer <strong id="delete-name"></strong>. Cette action est irréversible.</p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="closeModal('modal-delete')" style="flex:1;padding:12px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="delete-link" href="#" style="flex:1;padding:12px;border-radius:12px;background:var(--red);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Supprimer</a>
        </div>
    </div>
</div>

<!-- MODAL TOGGLE STATUT -->
<div id="modal-toggle" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)" onclick="closeModal('modal-toggle')"></div>
    <div style="position:relative;background:white;border-radius:20px;padding:36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .2s ease">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:64px;height:64px;border-radius:50%;background:#fffbeb;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-user-cog" style="font-size:24px;color:var(--gold)"></i>
            </div>
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px">Modifier le statut</h3>
            <p style="color:var(--text-muted);font-size:14px" id="toggle-desc"></p>
        </div>
        <div style="display:flex;gap:12px">
            <button onclick="closeModal('modal-toggle')" style="flex:1;padding:12px;border:1.5px solid #E5E7EB;border-radius:12px;background:white;cursor:pointer;font-size:14px;font-weight:600">Annuler</button>
            <a id="toggle-link" href="#" style="flex:1;padding:12px;border-radius:12px;background:var(--gold);color:white;text-align:center;text-decoration:none;font-size:14px;font-weight:600">Confirmer</a>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
const SITE_URL = '<?= SITE_URL ?>';
function openDeleteModal(id, name) {
    document.getElementById('delete-name').textContent = name;
    document.getElementById('delete-link').href = SITE_URL + '/admin/utilisateurs.php?action=supprimer&id=' + id;
    const m = document.getElementById('modal-delete');
    m.style.display = 'flex';
}
function openToggleModal(id, name, statut) {
    const action = statut === 'actif' ? 'suspendre' : 'réactiver';
    document.getElementById('toggle-desc').innerHTML = 'Vous allez <strong>' + action + '</strong> le compte de <strong>' + name + '</strong>.';
    document.getElementById('toggle-link').href = SITE_URL + '/admin/utilisateurs.php?action=toggle&id=' + id;
    document.getElementById('toggle-link').textContent = action.charAt(0).toUpperCase() + action.slice(1);
    const m = document.getElementById('modal-toggle');
    m.style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
