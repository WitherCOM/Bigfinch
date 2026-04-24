package main

import (
	"fmt"
	"log/slog"
	"net"
	"os"

	"github.com/go-mysql-org/go-mysql/server"
)

const (
	HOST = "localhost"
	PORT = "6666"
)

func createTcpServer() *net.Listener {
	slog.Debug("starting tcp server")
	if listener, err := net.Listen("tcp", HOST+":"+PORT); err == nil {
		slog.Info("tcp server started")
		return &listener
	} else {
		slog.Error(err.Error())
		os.Exit(1)
	}
	return nil
}

func handleConnection(conn *net.Conn, server *server.Server, db *BigfinchDb) {
	if dbConn, err := server.NewCustomizedConn(*conn, NewBigfinchAuthHandler(db), NewBigfinchHandler(db)); err == nil {
		for {
			if err := dbConn.HandleCommand(); err != nil {
				slog.Error(err.Error())
				break
			}
		}
	} else {
		slog.Error(err.Error())
	}
}

func main() {
	handler := slog.NewTextHandler(os.Stdout, &slog.HandlerOptions{
		Level: slog.LevelDebug, // set BEFORE anything logs
	})
	slog.SetDefault(slog.New(handler))
	slog.Info("starting interface")

	tcpServer := createTcpServer()
	db := NewBigfinchDb()
	dbServer := server.NewDefaultServer()
	defer (*tcpServer).Close()
	for {
		if conn, err := (*tcpServer).Accept(); err == nil {
			slog.Debug(fmt.Sprintf("%s connected", conn.RemoteAddr().String()))
			handleConnection(&conn, dbServer, db)
		} else {
			slog.Error(err.Error())
		}

	}
}
