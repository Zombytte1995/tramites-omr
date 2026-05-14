import * as XLSX from 'xlsx'
import type { Tramite } from '@/types'

const HEADERS = ['Código', 'Nombre', 'Descripción', 'Institución', 'Días Hábiles', 'Estado', 'Fecha de Creación']

/** Estilo del encabezado: negrita, tamaño 11, fondo gris claro — igual al backend. */
const HEADER_STYLE = {
  font: { bold: true, sz: 11 },
  fill: { patternType: 'solid', fgColor: { rgb: 'F1F5F9' } },
  border: {
    bottom: { style: 'thin', color: { rgb: 'CBD5E1' } },
  },
}

function tramiteToRow(t: Tramite): (string | number)[] {
  return [
    t.codigo,
    t.nombre,
    t.descripcion,
    t.institucion?.nombre ?? '—',
    t.dias_habiles,
    t.activo ? 'Activo' : 'Inactivo',
    t.created_at ? t.created_at.slice(0, 10).split('-').reverse().join('/') : '',
  ]
}

/**
 * Genera y descarga un archivo .xlsx con los trámites recibidos.
 * La generación ocurre íntegramente en el navegador usando SheetJS —
 * sin ninguna llamada al backend.
 *
 * El estilo del encabezado replica el del export de Laravel (negrita +
 * fondo gris F1F5F9 + columnas auto-ajustadas).
 *
 * @param tramites - Trámites a exportar (página actual del listado).
 * @param filename - Nombre base del archivo descargado (sin extensión).
 */
export function exportCsv(tramites: Tramite[], filename: string): void {
  if (tramites.length === 0) return

  // Construir la hoja con encabezados + datos
  const rows = tramites.map(tramiteToRow)
  const worksheet = XLSX.utils.aoa_to_sheet([HEADERS, ...rows])

  // Aplicar estilo al encabezado (fila 0)
  HEADERS.forEach((_, col) => {
    const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col })
    if (worksheet[cellAddress]) {
      worksheet[cellAddress].s = HEADER_STYLE
    }
  })

  // Calcular ancho de columnas basado en el contenido máximo de cada una
  const allRows = [HEADERS, ...rows]
  worksheet['!cols'] = HEADERS.map((_, col) => ({
    wch: Math.max(...allRows.map((row) => String(row[col] ?? '').length)) + 2,
  }))

  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Trámites')

  XLSX.writeFile(workbook, `${filename}.xlsx`, { bookType: 'xlsx', type: 'binary' })
}
