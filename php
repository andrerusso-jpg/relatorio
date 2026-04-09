import { useEffect, useState } from "react";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer } from "recharts";

export default function DashboardInscricoes() {
  const [dados, setDados] = useState([]);
  const [filtros, setFiltros] = useState({
    inicio: "",
    fim: "",
    nome: "",
    status: "",
  });

  const fetchDados = async () => {
    const res = await fetch("/api/inscricoes", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(filtros),
    });

    const json = await res.json();
    setDados(json);
  };

  useEffect(() => {
    fetchDados();
  }, [filtros]);

  const agruparPorData = () => {
    const mapa = {};
    dados.forEach((item) => {
      mapa[item.data_inscricao] = (mapa[item.data_inscricao] || 0) + 1;
    });

    return Object.keys(mapa).map((data) => ({
      data,
      total: mapa[data],
    }));
  };

  return (
    <div className="p-6 grid gap-6">
      <h1 className="text-2xl font-bold">Dashboard de Inscrições</h1>

      {/* Filtros */}
      <div className="grid grid-cols-4 gap-4">
        <Input
          type="date"
          onChange={(e) => setFiltros({ ...filtros, inicio: e.target.value })}
        />
        <Input
          type="date"
          onChange={(e) => setFiltros({ ...filtros, fim: e.target.value })}
        />
        <Input
          placeholder="Nome"
          onChange={(e) => setFiltros({ ...filtros, nome: e.target.value })}
        />
        <select
          className="border rounded p-2"
          onChange={(e) => setFiltros({ ...filtros, status: e.target.value })}
        >
          <option value="">Status</option>
          <option value="confirmado">Confirmado</option>
          <option value="pendente">Pendente</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>

      {/* Cards */}
      <div className="grid grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-4">
            <p>Total de Inscrições</p>
            <h2 className="text-2xl font-bold">{dados.length}</h2>
          </CardContent>
        </Card>
      </div>

      {/* Gráfico */}
      <div className="h-64">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={agruparPorData()}>
            <XAxis dataKey="data" />
            <YAxis />
            <Tooltip />
            <Bar dataKey="total" />
          </BarChart>
        </ResponsiveContainer>
      </div>

      {/* Tabela */}
      <table className="w-full border">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Status</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          {dados.map((item) => (
            <tr key={item.id}>
              <td>{item.id}</td>
              <td>{item.nome}</td>
              <td>{item.email}</td>
              <td>{item.status}</td>
              <td>{item.data_inscricao}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <Button onClick={() => window.print()}>Exportar PDF</Button>
    </div>
  );
}
