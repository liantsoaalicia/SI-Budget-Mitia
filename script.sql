DROP DATABASE budgetITU;

CREATE DATABASE budgetITU;

USE budgetITU;

-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS previsions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS departements;
DROP TABLE IF EXISTS periodes;

-- Create departements table
CREATE TABLE departements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Depense', 'Recette') NOT NULL,
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

-- Insert sample data for periodes (12 months of 2025)
INSERT INTO periodes (nom, date_debut, date_fin) VALUES
('Periode 1', '2025-01-01', '2025-01-31'),
('Periode 2', '2025-02-01', '2025-02-28'),
('Periode 3', '2025-03-01', '2025-03-31'),
('Periode 4', '2025-04-01', '2025-04-30'),
('Periode 5', '2025-05-01', '2025-05-31'),
('Periode 6', '2025-06-01', '2025-06-30'),
('Periode 7', '2025-07-01', '2025-07-31'),
('Periode 8', '2025-08-01', '2025-08-31'),
('Periode 9', '2025-09-01', '2025-09-30'),
('Periode 10', '2025-10-01', '2025-10-31'),
('Periode 11', '2025-11-01', '2025-11-30'),
('Periode 12', '2025-12-01', '2025-12-31');



INSERT INTO departements (nom) VALUES 
('Département Administratif'),
('Département Disciplinaire'),
('Département de l''Enseignement et de la Pédagogie'),
('Département de Recherche et Développement'),
('Département Réseaux et Maintenance'),
('Département ou Service d''Entretien'),
('Département ou Service de Sécurité'),
('Département des équipements'),
('Département Multimédia et Réseaux Sociaux');


-- Insert sample categories
INSERT INTO categories (type, charge_type, nature, source, destination) VALUES
-- Département Administratif
('Recette', 'Charges fixes', 'Frais de scolarité', 'Étudiants', 'Financement des opérations administratives'),
('Dépense', 'Charges fixes', 'Salaires et Charges sociales', 'Budget université', 'Personnel administratif'),

-- Département Disciplinaire
('Recette', 'Charges fixes', 'Frais de certification', 'Étudiants, entreprises partenaires', 'Financement des programmes disciplinaires'),
('Dépense', 'Charges variables', 'Achat de matériel pédagogique', 'Budget université, subventions', 'Étudiants, enseignants'),

-- Département de l'Enseignement et de la Pédagogie
('Recette', 'Charges fixes', 'Salaires et Charges sociales', 'Budget université', 'Financement des programmes pédagogiques'),
('Dépense', 'Charges variables', 'Formation des enseignants', 'Budget université', 'Enseignants'),
('Dépense', 'Charges fixes', 'Développement de curricula', 'Subventions', 'Étudiants, programmes académiques'),
('Dépense', 'Charges variables', 'Organisation d''événements pédagogiques', 'Budget université, sponsors', 'Étudiants, communauté universitaire'),

-- Département de Recherche et Développement
('Dépense', 'Charges variables', 'Création et gestion de projets', 'Budget université, subventions', 'Projets de recherche'),

-- Département Réseaux et Maintenance
('Dépense', 'Charges variables', 'Matériels de maintenance', 'Budget université, subventions', 'Infrastructures, étudiants, personnel'),
('Dépense', 'Charges fixes', 'Maintenance du réseau Wi-Fi', 'Budget université', 'Étudiants, personnel, visiteurs'),
('Dépense', 'Charges variables', 'Achat d''ordinateurs pour étudiants', 'Budget université, dons, subventions', 'Étudiants (prêt ou utilisation en salle)'),
('Dépense', 'Charges variables', 'Maintenance des ordinateurs', 'Budget université', 'Étudiants, laboratoires'),
('Dépense', 'Charges fixes', 'Salaires du personnel technique', 'Budget université', 'Personnel du département'),

-- Département ou Service d'Entretien
('Dépense', 'Charges variables', 'Fournitures électroménagers', 'Budget université', 'Maintenance des infrastructures'),
('Dépense', 'Charges fixes', 'Salaires', 'Budget université', 'Personnel d''entretien'),

-- Département ou Service de Sécurité
('Dépense', 'Charges fixes', 'Salaires', 'Budget université', 'Agents de sécurité'),
('Dépense', 'Charges variables', 'Équipements de sécurité (caméras, alarmes)', 'Budget université', 'Sécurité des étudiants et du personnel'),

-- Département des équipements
('Dépense', 'Charges fixes', 'Fournitures scolaires', 'Budget université', 'Étudiants'),
('Dépense', 'Charges variables', 'Achat de matériel informatique', 'Budget université, subventions', 'Étudiants, laboratoires'),

-- Département Multimédia et Réseaux Sociaux
('Dépense', 'Charges fixes', 'Salaires', 'Budget université', 'Personnel multimédia'),
('Dépense', 'Charges variables', 'Achat d''équipements multimédias (caméras, micros, logiciels)', 'Budget université', 'Communication interne et externe'),
('Dépense', 'Charges variables', 'Gestion des réseaux sociaux', 'Budget université', 'Promotion de l''université');


INSERT INTO soldes (departement_id, periode_id, solde_debut, solde_fin) VALUES
(1, 1, 50000000.00, NULL);


