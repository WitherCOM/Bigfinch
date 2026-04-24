package main

import (
	"database/sql"
	"fmt"
	"log/slog"
	"os"
	"strconv"
	"time"

	_ "modernc.org/sqlite"
)

type BigfinchDb struct {
	db *sql.DB
}

func NewBigfinchDb() *BigfinchDb {
	return &BigfinchDb{
		db: openDB(),
	}
}

func openDB() *sql.DB {
	slog.Debug("connecting to db")
	dbType := GetEnv("DB_CONNECTION", "sqlite")
	if dbType != "sqlite" {
		dbHost := GetEnv("DB_HOST", "127.0.0.1")
		var dbPort int
		if port, err := strconv.Atoi(GetEnv("DB_PORT", "3306")); err == nil {
			dbPort = port
		} else {
			slog.Error(err.Error())
			os.Exit(1)
		}
		dbName := GetEnv("DB_DATABASE", "laravel")
		dbUser := GetEnv("DB_USER", "root")
		dbPassword := GetEnv("DB_PASSWORD", "")
		connStr := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s sslmode=disable", dbHost, dbPort, dbUser, dbPassword, dbName)
		if db, err := sql.Open(dbType, connStr); err == nil {
			slog.Info("connected to database")
			return db
		} else {
			slog.Error(err.Error())
			os.Exit(1)
		}
	} else {
		dbName := GetEnv("DB_DATABASE", "../database/database.sqlite")
		if db, err := sql.Open("sqlite", dbName); err == nil {
			slog.Info("connected to database")
			return db
		} else {
			slog.Error(err.Error())
			os.Exit(1)
		}
	}
	return nil
}

func (b BigfinchDb) precheckDb() {
	if err := b.db.Ping(); err != nil {
		slog.Error(fmt.Sprintf("%s, shutting down", err.Error()))
		os.Exit(1)
	}
}

func (b BigfinchDb) GetValidKeysForUser(email string) []string {
	b.precheckDb()
	if rows, err := b.db.Query("SELECT a.api_key FROM api_keys a INNER JOIN users u on a.user_id = u.id WHERE u.email = ? AND a.expire_at > ?;", email, time.Now()); err == nil {
		defer rows.Close()
		keys := make([]string, 0)
		for rows.Next() {
			var key string
			if err := rows.Scan(&key); err != nil {
				break
			}
			keys = append(keys, key)
		}
		return keys
	} else {
		return []string{}
	}
}
