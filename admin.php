<?php
// admin.php
session_start(); // Démarrage de la session

// Vérification de l'authentification
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php'); // Redirection vers la page de connexion
    exit; // Arrêt du script
} else {
    // Optionnel: Rafraîchir la session pour prolonger la durée de vie
    $_SESSION['last_activity'] = time();
}

// Connexion à la base de données (à adapter selon votre configuration)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=caida_db;charset=utf8', 'username', 'password'); // Remplacez par vos identifiants
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
} // Fin de la connexion à la base de données

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_actualite':
                addActualite($pdo);
                break;
            case 'edit_actualite':
                editActualite($pdo);
                break;
            case 'delete_actualite':
                deleteActualite($pdo);
                break;
            case 'add_formation':
                addFormation($pdo);
                break;
            case 'edit_formation':
                editFormation($pdo);
                break;
            case 'delete_formation':
                deleteFormation($pdo);
                break;
            case 'delete_inscription':
                deleteInscription($pdo);
                break;
        }
    }
} // Fin du traitement des actions

// Fonctions de gestion des actualités
function addActualite($pdo) {
    $stmt = $pdo->prepare("INSERT INTO actualites (titre, image, date_publication, auteur, description, contenu) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['titre'],
        $_POST['image'],
        $_POST['date_publication'],
        $_POST['auteur'],
        $_POST['description'],
        $_POST['contenu']
    ]);
    $_SESSION['message'] = "Actualité ajoutée avec succès!";
}

function editActualite($pdo) {
    $stmt = $pdo->prepare("UPDATE actualites SET titre=?, image=?, date_publication=?, auteur=?, description=?, contenu=? WHERE id=?");
    $stmt->execute([
        $_POST['titre'],
        $_POST['image'],
        $_POST['date_publication'],
        $_POST['auteur'],
        $_POST['description'],
        $_POST['contenu'],
        $_POST['id']
    ]);
    $_SESSION['message'] = "Actualité modifiée avec succès!";
}

function deleteActualite($pdo) {
    $stmt = $pdo->prepare("DELETE FROM actualites WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $_SESSION['message'] = "Actualité supprimée avec succès!";
}

// Fonctions de gestion des formations
function addFormation($pdo) {
    $stmt = $pdo->prepare("INSERT INTO formations (titre, image, date_debut, date_fin, lieu, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['titre'],
        $_POST['image'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['lieu'],
        $_POST['description']
    ]);
    $_SESSION['message'] = "Formation ajoutée avec succès!";
}

function editFormation($pdo) {
    $stmt = $pdo->prepare("UPDATE formations SET titre=?, image=?, date_debut=?, date_fin=?, lieu=?, description=? WHERE id=?");
    $stmt->execute([
        $_POST['titre'],
        $_POST['image'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['lieu'],
        $_POST['description'],
        $_POST['id']
    ]);
    $_SESSION['message'] = "Formation modifiée avec succès!";
}

function deleteFormation($pdo) {
    $stmt = $pdo->prepare("DELETE FROM formations WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $_SESSION['message'] = "Formation supprimée avec succès!";
}

function deleteInscription($pdo) {
    $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $_SESSION['message'] = "Inscription supprimée avec succès!";
}

// Récupération des données
$actualites = $pdo->query("SELECT * FROM actualites ORDER BY date_publication DESC")->fetchAll();
$formations = $pdo->query("SELECT * FROM formations ORDER BY date_debut DESC")->fetchAll();
$inscriptions = $pdo->query("SELECT i.*, f.titre as formation_titre FROM inscriptions i LEFT JOIN formations f ON i.formation_id = f.id ORDER BY i.date_inscription DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - CAIDA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #218838;
            --secondary-color: #155724;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --text-dark: #212529;
            --text-light: #6c757d;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
            --transition: all 0.4s ease;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background: var(--secondary-color);
            color: white;
            min-height: 100vh;
            position: fixed;
        }
        
        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: var(--transition);
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-admin {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            transition: var(--transition);
        }
        
        .btn-admin:hover {
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mb-4">
                        <img src="logo_CAIDA.png" alt="CAIDA" style="height: 80px; background: white; padding: 10px; border-radius: 10px;">
                        <h5 class="mt-3">Administration CAIDA</h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#actualites" data-toggle="tab">
                                <i class="fa fa-newspaper-o"></i> Actualités
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#formations" data-toggle="tab">
                                <i class="fa fa-graduation-cap"></i> Formations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#inscriptions" data-toggle="tab">
                                <i class="fa fa-users"></i> Inscriptions
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-warning" href="admin_logout.php">
                                <i class="fa fa-sign-out"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 main-content">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Onglet Actualités -->
                    <div class="tab-pane fade show active" id="actualites">
                        <div class="admin-header">
                            <h2>Gestion des Actualités</h2>
                            <p class="mb-0">Ajoutez, modifiez ou supprimez les actualités du site</p>
                        </div>

                        <!-- Formulaire d'ajout/modification -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0" id="form-actualite-title">Ajouter une actualité</h5>
                            </div>
                            <div class="card-body">
                                <form id="formActualite" method="POST">
                                    <input type="hidden" name="action" id="actualite-action" value="add_actualite">
                                    <input type="hidden" name="id" id="actualite-id">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="titre">Titre</label>
                                                <input type="text" class="form-control" id="titre" name="titre" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="image">URL de l'image</label>
                                                <input type="text" class="form-control" id="image" name="image" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_publication">Date de publication</label>
                                                <input type="date" class="form-control" id="date_publication" name="date_publication" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="auteur">Auteur</label>
                                                <input type="text" class="form-control" id="auteur" name="auteur" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description">Description courte</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="contenu">Contenu complet</label>
                                        <textarea class="form-control" id="contenu" name="contenu" rows="6" required></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-admin">Enregistrer</button>
                                    <button type="button" class="btn btn-secondary" id="btn-cancel" style="display: none;">Annuler</button>
                                </form>
                            </div>
                        </div>

                        <!-- Liste des actualités -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Liste des actualités</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Titre</th>
                                                <th>Date</th>
                                                <th>Auteur</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($actualites as $actualite): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($actualite['titre']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($actualite['date_publication'])) ?></td>
                                                <td><?= htmlspecialchars($actualite['auteur']) ?></td>
                                                <td>
                                                    <button class="btn btn-admin btn-edit btn-sm" onclick="editActualite(<?= htmlspecialchars(json_encode($actualite)) ?>)">Modifier</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_actualite">
                                                        <input type="hidden" name="id" value="<?= $actualite['id'] ?>">
                                                        <button type="submit" class="btn btn-admin btn-delete btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Formations -->
                    <div class="tab-pane fade" id="formations">
                        <div class="admin-header">
                            <h2>Gestion des Formations</h2>
                            <p class="mb-0">Gérez les formations et ateliers proposés par CAIDA</p>
                        </div>

                        <!-- Formulaire d'ajout/modification -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0" id="form-formation-title">Ajouter une formation</h5>
                            </div>
                            <div class="card-body">
                                <form id="formFormation" method="POST">
                                    <input type="hidden" name="action" id="formation-action" value="add_formation">
                                    <input type="hidden" name="id" id="formation-id">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="formation_titre">Titre</label>
                                                <input type="text" class="form-control" id="formation_titre" name="titre" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="formation_image">URL de l'image</label>
                                                <input type="text" class="form-control" id="formation_image" name="image" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="date_debut">Date de début</label>
                                                <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="date_fin">Date de fin</label>
                                                <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="lieu">Lieu</label>
                                                <input type="text" class="form-control" id="lieu" name="lieu" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="formation_description">Description</label>
                                        <textarea class="form-control" id="formation_description" name="description" rows="4" required></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-admin">Enregistrer</button>
                                    <button type="button" class="btn btn-secondary" id="btn-cancel-formation" style="display: none;">Annuler</button>
                                </form>
                            </div>
                        </div>

                        <!-- Liste des formations -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Liste des formations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Titre</th>
                                                <th>Dates</th>
                                                <th>Lieu</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($formations as $formation): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($formation['titre']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($formation['date_debut'])) ?> - <?= date('d/m/Y', strtotime($formation['date_fin'])) ?></td>
                                                <td><?= htmlspecialchars($formation['lieu']) ?></td>
                                                <td>
                                                    <button class="btn btn-admin btn-edit btn-sm" onclick="editFormation(<?= htmlspecialchars(json_encode($formation)) ?>)">Modifier</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_formation">
                                                        <input type="hidden" name="id" value="<?= $formation['id'] ?>">
                                                        <button type="submit" class="btn btn-admin btn-delete btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette formation ?')">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Inscriptions -->
                    <div class="tab-pane fade" id="inscriptions">
                        <div class="admin-header">
                            <h2>Gestion des Inscriptions</h2>
                            <p class="mb-0">Consultez et gérez les inscriptions aux formations</p>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Liste des inscriptions</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Téléphone</th>
                                                <th>Formation</th>
                                                <th>Date d'inscription</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inscriptions as $inscription): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom']) ?></td>
                                                <td><?= htmlspecialchars($inscription['email']) ?></td>
                                                <td><?= htmlspecialchars($inscription['telephone']) ?></td>
                                                <td><?= htmlspecialchars($inscription['formation_titre']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($inscription['date_inscription'])) ?></td>
                                                <td>
                                                    <button class="btn btn-admin btn-sm" onclick="viewInscription(<?= htmlspecialchars(json_encode($inscription)) ?>)">Voir</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_inscription">
                                                        <input type="hidden" name="id" value="<?= $inscription['id'] ?>">
                                                        <button type="submit" class="btn btn-admin btn-delete btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour voir les détails d'inscription -->
    <div class="modal fade" id="inscriptionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'inscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="inscription-details">
                    <!-- Les détails seront insérés ici -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Gestion des actualités
        function editActualite(actualite) {
            document.getElementById('form-actualite-title').textContent = 'Modifier l\'actualité';
            document.getElementById('actualite-action').value = 'edit_actualite';
            document.getElementById('actualite-id').value = actualite.id;
            document.getElementById('titre').value = actualite.titre;
            document.getElementById('image').value = actualite.image;
            document.getElementById('date_publication').value = actualite.date_publication;
            document.getElementById('auteur').value = actualite.auteur;
            document.getElementById('description').value = actualite.description;
            document.getElementById('contenu').value = actualite.contenu;
            document.getElementById('btn-cancel').style.display = 'inline-block';
            
            document.getElementById('formActualite').scrollIntoView({ behavior: 'smooth' });
        }

        document.getElementById('btn-cancel').addEventListener('click', function() {
            resetActualiteForm();
        });

        function resetActualiteForm() {
            document.getElementById('form-actualite-title').textContent = 'Ajouter une actualité';
            document.getElementById('actualite-action').value = 'add_actualite';
            document.getElementById('actualite-id').value = '';
            document.getElementById('formActualite').reset();
            document.getElementById('btn-cancel').style.display = 'none';
        }

        // Gestion des formations
        function editFormation(formation) {
            document.getElementById('form-formation-title').textContent = 'Modifier la formation';
            document.getElementById('formation-action').value = 'edit_formation';
            document.getElementById('formation-id').value = formation.id;
            document.getElementById('formation_titre').value = formation.titre;
            document.getElementById('formation_image').value = formation.image;
            document.getElementById('date_debut').value = formation.date_debut;
            document.getElementById('date_fin').value = formation.date_fin;
            document.getElementById('lieu').value = formation.lieu;
            document.getElementById('formation_description').value = formation.description;
            document.getElementById('btn-cancel-formation').style.display = 'inline-block';
            
            document.getElementById('formFormation').scrollIntoView({ behavior: 'smooth' });
        }

        document.getElementById('btn-cancel-formation').addEventListener('click', function() {
            resetFormationForm();
        });

        function resetFormationForm() {
            document.getElementById('form-formation-title').textContent = 'Ajouter une formation';
            document.getElementById('formation-action').value = 'add_formation';
            document.getElementById('formation-id').value = '';
            document.getElementById('formFormation').reset();
            document.getElementById('btn-cancel-formation').style.display = 'none';
        }

        // Voir les détails d'inscription
        function viewInscription(inscription) {
            let details = `
                <p><strong>Nom:</strong> ${inscription.prenom} ${inscription.nom}</p>
                <p><strong>Email:</strong> ${inscription.email}</p>
                <p><strong>Téléphone:</strong> ${inscription.telephone}</p>
                <p><strong>Formation:</strong> ${inscription.formation_titre}</p>
                <p><strong>Organisation:</strong> ${inscription.entreprise || 'Non renseigné'}</p>
                <p><strong>Date d'inscription:</strong> ${new Date(inscription.date_inscription).toLocaleString('fr-FR')}</p>
            `;
            
            if (inscription.message) {
                details += `<p><strong>Message:</strong> ${inscription.message}</p>`;
            }
            
            document.getElementById('inscription-details').innerHTML = details;
            $('#inscriptionModal').modal('show');
        }

        // Navigation par onglets
        $('.sidebar .nav-link').click(function(e) {
            e.preventDefault();
            $('.sidebar .nav-link').removeClass('active');
            $(this).addClass('active');
        });
    </script>
</body>
</html>