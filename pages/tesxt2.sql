TO DO LIST SI :
liste page a faire : 
    -insertion categories et liste categories par departement
    - visualiser prevision et modifier prevision, pour chaque depense et recette des departemens
    par periode
    - insertion valeur de depense ou de recette par departement :
        selection departement
        depense  ou recette 
        quelle periode
        valeur ou insertion solde debut d une periode
    - voir budget global (tous les departemens plsu prevision plus ecart)
    - voir budget par departement 
    budget : valeur plus total depense , total recette  
lorsqu on peux calculer le solde fin d une periode , ce solde fin devient automatiquement le nouveau solde de debut du prevision du prochain periode
voici une loite de donnes qu on devrait pourvoir enter dans la base 
Departement	categories	types	nature	source	destination
Département de Recherche et Développement (LABS, …)	Dépense	Charges variables	Création et gestion de projets	Subventions, contrats de recherche	Financement des projets de recherche, salaires des chercheurs
Département Réseaux et Maintenance	Dépense	Charges variables	Matériels de maintenance	Budget université, subventions	Étudiants, personnel, infrastructures
Département Administratif	Recette	Charges fixes	Frais de scolarité	Étudiants	Fonctionnement du département

voic comment on devrait affciehr le budget global : 	Periode 1			Periode 2			
Resusltat	Prevision	Realisation	Ecart(difference ) entre prevision et realisation	Prevision	Realisation	Ecart	jusqu a periode 12
Rubrique 							
Solde Debut							
Departement? 							
Categorie Recette type , nature 							
Categorie Depense type , nature 							
Solde fIN							
esnuite creer les pages php correspandante avec la connesion mysql, si poosible assemlbe plusieur page en une seule en utilisant le javascript , pour les pages ajoute classe bootrap 5 
voici les tables:-- Create departements table
CREATE TABLE departements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Dépense', 'Recette') NOT NULL,
    charge_type ENUM('Charges fixes', 'Charges variables') NOT NULL,
    nature VARCHAR(255) NOT NULL,
    source VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Create periodes table
CREATE TABLE periodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    UNIQUE(nom)
);
-- Create previsions table
CREATE TABLE previsions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    categorie_id INT NOT NULL,
    periode_id INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE,
    UNIQUE(departement_id, categorie_id, periode_id)
);
-- Create transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    categorie_id INT NOT NULL,
    periode_id INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE
);
-- Create soldes table to track beginning and ending balances for each period and department
CREATE TABLE soldes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    periode_id INT NOT NULL,
    solde_debut DECIMAL(15,2) NOT NULL DEFAULT 0,
    solde_fin DECIMAL(15,2) DEFAULT NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE,
    UNIQUE(departement_id, periode_id)
);


creer moi insert into pour ce donne en fonciton de ce table : 
Departement	categories	types	nature	source	destination
Département de Recherche et Développement (LABS, …)	Recette	Charges fixes	Frais de scolarité	Étudiants	Fonctionnement du département
Département Réseaux et Maintenance	Recette	Charges fixes	Frais de certification	Étudiants, entreprises partenaires	Financement des programmes disciplinaires
Département Administratif	Recette	Charges fixes	Salaires et charges sociales	Bugdet université	Financement des programmes pédagogiques
Département Administratif	Dépense	Charges variables	Création et gestion de projets	Subventions, contrats de recherche	Financement des projets de recherche, salaires des chercheurs
Département Disciplinaire	Dépense	Charges variables	Matériels de maintenance	Budget université, subventions	Étudiants, personnel, infrastructures
Département Disciplinaire	Dépense	Charges fixes	Salaires et charges sociales	Budget université	Personnel administratif
Département de l'Enseignement et de la Pédagogie	Dépense	Charges variables	Achat de matériel pédagogique	Budget université, subventions	Étudiants, enseignants
Département de l'Enseignement et de la Pédagogie	Dépense	Charges variables	Formation des enseignants	Budget université	Enseignants
Département de l'Enseignement et de la Pédagogie	Dépense	Charges fixes	Développement de curricula	Subventions	Étudiants, programmes académiques
Département de l'Enseignement et de la Pédagogie	Dépense	Charges variables	Organisation d'événements pédagogiques	Budget université, sponsors	Étudiants, communauté universitaire
Département ou Service d'Entretien	Dépense	Charges variables	Fournitures électroménagers	Budget université	Maintenance des infrastructures
Département ou Service d'Entretien	Dépense	Charges fixes	Salaires	Budget université	Personnel d'entretien
Département ou Service de Sécurité	Dépense	Charges fixes	Salaires	Budget université	Agents de sécurité
Département ou Service de Sécurité	Dépense	Charges variables	Équipements de sécurité (caméras, alarmes)	Budget université	Sécurité des étudiants et du personnel
Département des Équipements	Dépense	Charges fixes	Fournitures scolaires	Budget université	Étudiants
Département des Équipements	Dépense	Charges variables	Achat de matériel informatique	Budget université, subventions	Étudiants, laboratoires
Département Multimédia et Réseaux Sociaux	Dépense	Charges fixes	Salaires	Budget université	Personnel multimédia
Département Multimédia et Réseaux Sociaux	Dépense	Charges variables	Achat d'équipements multimédias (caméras, micros, logiciels)	Budget université	Communication interne et externe
Département Multimédia et Réseaux Sociaux	Dépense	Charges variables	Gestion des réseaux sociaux	Budget université	Promotion de l'université
Département Réseaux et Maintenance	Dépense	Charges variables	Matériels de maintenance	Budget université, subventions	Étudiants, personnel, infrastructures
Département Réseaux et Maintenance	Dépense	Charges fixes	Maintenance du réseau Wi-Fi	Budget université	Étudiants, personnel, visiteurs
Département Réseaux et Maintenance	Dépense	Charges variables	Achat d'ordinateurs pour étudiants	Budget université, dons, subventions	Étudiants (prêt ou utilisation en salle)
Département Réseaux et Maintenance	Dépense	Charges variables	Maintenance des ordinateurs	Budget université	Étudiants, laboratoires
Département Réseaux et Maintenance	Dépense	Charges fixes	Salaires du personnel technique	Budget université	Personnel du département

voici des inserto into que j ai deja fait ne les faits plus 
-- Insert sample departments
INSERT INTO departements (nom) VALUES
('Département de Recherche et Développement'),
('Département Réseaux et Maintenance'),
('Département Administratif');

-- Insert sample categories
INSERT INTO categories (type, charge_type, nature, source, destination) VALUES
('Dépense', 'Charges variables', 'Création et gestion de projets', 'Subventions, contrats de recherche', 'Financement des projets de recherche, salaires des chercheurs'),
('Dépense', 'Charges variables', 'Matériels de maintenance', 'Budget université, subventions', 'Étudiants, personnel, infrastructures'),
('Recette', 'Charges fixes', 'Frais de scolarité', 'Étudiants', 'Fonctionnement du département');

