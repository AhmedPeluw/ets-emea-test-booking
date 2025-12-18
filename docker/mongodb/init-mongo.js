// Script d'initialisation MongoDB pour ETS EMEA Test Booking

db = db.getSiblingDB('ets_booking');

// Créer les collections
db.createCollection('users');
db.createCollection('sessions');
db.createCollection('bookings');

// Créer les indexes pour optimiser les performances
db.users.createIndex({ "email": 1 }, { unique: true });
db.sessions.createIndex({ "language": 1, "date": -1 });
db.sessions.createIndex({ "date": -1 });
db.bookings.createIndex({ "userId": 1, "sessionId": 1 }, { unique: true });
db.bookings.createIndex({ "userId": 1, "createdAt": -1 });
db.bookings.createIndex({ "sessionId": 1 });

print('✅ Collections et indexes créés avec succès!');

// Insérer des données de test (optionnel)
const testUser = {
    name: "Test User",
    email: "test@example.com",
    password: "$2y$13$hashedPasswordExample", // password: test123
    roles: ["ROLE_USER"],
    createdAt: new Date(),
    updatedAt: new Date()
};

const adminUser = {
    name: "Admin User",
    email: "admin@example.com",
    password: "$2y$13$hashedPasswordExample", // password: admin123
    roles: ["ROLE_USER", "ROLE_ADMIN"],
    createdAt: new Date(),
    updatedAt: new Date()
};

// Insérer les utilisateurs de test
try {
    db.users.insertMany([testUser, adminUser]);
    print('✅ Utilisateurs de test créés');
} catch(e) {
    print('ℹ️ Utilisateurs de test déjà existants');
}

// Insérer quelques sessions de test
const sessions = [
    {
        language: "Anglais",
        date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000), // Dans 7 jours
        time: "09:00",
        location: "Paris - Centre ETS",
        totalSeats: 20,
        availableSeats: 20,
        description: "Test d'anglais niveau avancé",
        level: "C1",
        durationMinutes: 120,
        price: 150.00,
        isActive: true,
        createdAt: new Date(),
        updatedAt: new Date()
    },
    {
        language: "Français",
        date: new Date(Date.now() + 10 * 24 * 60 * 60 * 1000), // Dans 10 jours
        time: "14:00",
        location: "Lyon - Centre ETS",
        totalSeats: 15,
        availableSeats: 15,
        description: "Test de français général",
        level: "B2",
        durationMinutes: 120,
        price: 120.00,
        isActive: true,
        createdAt: new Date(),
        updatedAt: new Date()
    },
    {
        language: "Espagnol",
        date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000), // Dans 14 jours
        time: "10:30",
        location: "Marseille - Centre ETS",
        totalSeats: 12,
        availableSeats: 12,
        description: "Test d'espagnol intermédiaire",
        level: "B1",
        durationMinutes: 90,
        price: 100.00,
        isActive: true,
        createdAt: new Date(),
        updatedAt: new Date()
    }
];

try {
    db.sessions.insertMany(sessions);
    print('✅ Sessions de test créées');
} catch(e) {
    print('ℹ️ Sessions de test déjà existantes');
}

print('🎉 Initialisation MongoDB terminée!');
