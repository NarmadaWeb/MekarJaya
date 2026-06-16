import os
import shutil
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioDiagramBuilder:
    ST_SWIMLANE = "swimlane;startSize=30;html=1;whiteSpace=wrap;collapsible=0;connectable=0;container=1;pointerEvents=0;fillColor=none;strokeColor=#000000;fontStyle=1;align=center;fontSize=13;fontColor=#000000;strokeWidth=1.5;"
    ST_START = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_END_OUTER = "ellipse;html=1;fillColor=none;strokeColor=#000000;strokeWidth=2;aspect=fixed;"
    ST_END_INNER = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_ACTION = "rounded=1;whiteSpace=wrap;html=1;arcSize=20;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    ST_RHOMBUS = "rhombus;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    
    EDGE_ORTHO = "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.5;fontSize=12;labelBackgroundColor=#FFFFFF;fontColor=#000000;"

    def __init__(self, name, page_id, width=900, height=900):
        self.name = name
        self.page_id = page_id
        self.width = width
        self.height = height
        self.nodes = []
        self.edges = []
        self.counter = 0

    def _next_id(self, prefix="elem"):
        self.counter += 1
        return f"{prefix}_{self.page_id}_{self.counter}"

    def add_node(self, nid, label, style, x, y, w, h, parent="1"):
        self.nodes.append({
            "id": nid, "value": label, "style": style,
            "x": x, "y": y, "w": w, "h": h, "parent": parent
        })

    def link_nodes(self, src, dst, label="", style="", points=None, exit_pt=None, entry_pt=None):
        edge_style = style or self.EDGE_ORTHO
        if exit_pt:
            edge_style += f"exitX={exit_pt[0]};exitY={exit_pt[1]};exitDx=0;exitDy=0;"
        if entry_pt:
            edge_style += f"entryX={entry_pt[0]};entryY={entry_pt[1]};entryDx=0;entryDy=0;"

        self.edges.append({
            "id": self._next_id("edge"), "value": label, "style": edge_style,
            "source": src, "target": dst, "points": points or []
        })

    def save(self, filepath):
        mxfile = ET.Element("mxfile", host="Electron", version="21.6.8",
                            modified="2026-06-15T10:00:00.000Z",
                            agent="Mozilla/5.0", type="device")
        diagram = ET.SubElement(mxfile, "diagram", id=self.page_id, name=self.name)
        model = ET.SubElement(diagram, "mxGraphModel",
                              dx="1000", dy="1000", grid="1", gridSize="10",
                              guides="1", tooltips="1", connect="1", arrows="1",
                              fold="1", page="1", pageScale="1",
                              pageWidth=str(self.width), pageHeight=str(self.height),
                              math="0", shadow="0")
        root = ET.SubElement(model, "root")
        
        ET.SubElement(root, "mxCell", id="0")
        ET.SubElement(root, "mxCell", id="1", parent="0")

        # Nodes
        for n in self.nodes:
            cell = ET.SubElement(root, "mxCell", id=n["id"], value=n["value"],
                                 style=n["style"], vertex="1", parent=n["parent"])
            geom = ET.SubElement(cell, "mxGeometry",
                                 x=str(n["x"]), y=str(n["y"]),
                                 width=str(n["w"]), height=str(n["h"]))
            geom.set("as", "geometry")

        # Edges
        for e in self.edges:
            cell = ET.SubElement(root, "mxCell", id=e["id"], value=e["value"],
                                 style=e["style"], edge="1", parent="1",
                                 source=e["source"], target=e["target"])
            geom = ET.SubElement(cell, "mxGeometry", relative="1")
            geom.set("as", "geometry")
            if e["points"]:
                arr = ET.SubElement(geom, "Array")
                arr.set("as", "points")
                for (px, py) in e["points"]:
                    ET.SubElement(arr, "mxPoint", x=str(px), y=str(py))

        raw = ET.tostring(mxfile, encoding="utf-8")
        dom = minidom.parseString(raw)
        pretty_xml = dom.toprettyxml(indent="    ")
        pretty_xml = "\n".join([line for line in pretty_xml.split("\n") if line.strip()])
        if pretty_xml.startswith("<?xml"):
            pretty_xml = pretty_xml.split("\n", 1)[1]
        out_content = '<?xml version="1.0" encoding="UTF-8"?>\n' + pretty_xml

        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(out_content)


def generate_activities(dest_dir):
    # Clear directory first to keep it clean (exactly 10 files)
    if os.path.exists(dest_dir):
        shutil.rmtree(dest_dir)
    os.makedirs(dest_dir)

    print("Generating 10 Activity Diagrams...")

    # ================== ADMIN DIAGRAMS (5 File) ==================

    # 1. Admin Login
    b = DrawioDiagramBuilder("Activity Login Admin", "act-adm-login", 900, 650)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 360, 560)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 560)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Mengakses Halaman Login Admin\ndan Mengisi Email & Password", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memvalidasi Kredensial Admin", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Kredensial\nValid?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Pesan Error\nKredensial Salah", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Membuat Session Admin\ndan Mengarahkan ke Dashboard", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 442, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 449, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "a1", points=[(780, 367), (780, 110), (240, 110)])
    b.link_nodes("s2_ok", "final")
    b.save(os.path.join(dest_dir, "admin_login.drawio"))

    # 2. Admin Kelola Produk (CRUD)
    b = DrawioDiagramBuilder("Activity Kelola Produk", "act-adm-kelola-prod", 1000, 950)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 400, 860)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 520, 40, 400, 860)
    b.add_node("start", "", b.ST_START, 245, 80, 30, 30)
    b.add_node("a1", "Membuka Halaman Kelola Produk", b.ST_ACTION, 160, 140, 200, 55)
    b.add_node("s1", "Mengambil Data Produk & Menampilkan\nTabel Produk (Tambah, Edit, Hapus)", b.ST_ACTION, 620, 140, 200, 55)
    b.add_node("dec_aksi", "Pilih Aksi?", b.ST_RHOMBUS, 200, 230, 120, 75)
    
    # Tambah Flow
    b.add_node("a_tambah", "Mengisi Form & Kirim Produk Baru", b.ST_ACTION, 100, 340, 180, 55)
    b.add_node("s_tambah", "Validasi, Upload Gambar &\nSimpan Produk Baru ke DB", b.ST_ACTION, 540, 340, 180, 55)
    
    # Edit Flow
    b.add_node("a_edit", "Mengubah Data Form & Kirim Update", b.ST_ACTION, 100, 430, 180, 55)
    b.add_node("s_edit", "Validasi & Update Data Produk di DB", b.ST_ACTION, 540, 430, 180, 55)
    
    # Hapus Flow
    b.add_node("a_hapus", "Konfirmasi Hapus Produk", b.ST_ACTION, 100, 520, 180, 55)
    b.add_node("s_hapus", "Hapus Data Produk dari DB &\nHapus Gambar di Server", b.ST_ACTION, 540, 520, 180, 55)
    
    # Done / Back Flow
    b.add_node("a_kembali", "Kembali ke Dashboard", b.ST_ACTION, 100, 610, 180, 55)
    
    # Redirect state
    b.add_node("s_refresh", "Me-refresh Halaman & Menampilkan Notifikasi Sukses", b.ST_ACTION, 620, 700, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 245, 712, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 252, 719, 16, 16)

    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec_aksi", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(720, 210), (260, 210)])
    
    # Routes from Decision Aksi
    b.link_nodes("dec_aksi", "a_tambah", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(190, 267)])
    b.link_nodes("dec_aksi", "a_edit", label="Edit", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(190, 267)])
    b.link_nodes("dec_aksi", "a_hapus", label="Hapus", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(190, 267)])
    b.link_nodes("dec_aksi", "a_kembali", label="Kembali", exit_pt=(0.5, 1), entry_pt=(0.5, 0))

    # Link from actions to system processes
    b.link_nodes("a_tambah", "s_tambah")
    b.link_nodes("a_edit", "s_edit")
    b.link_nodes("a_hapus", "s_hapus")
    
    # Link from processes to refresh
    b.link_nodes("s_tambah", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(630, 410), (720, 410)])
    b.link_nodes("s_edit", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(630, 500), (720, 500)])
    b.link_nodes("s_hapus", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(630, 590), (720, 590)])
    
    # Loop back from refresh to table view
    b.link_nodes("s_refresh", "s1", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(960, 727), (960, 167)])
    
    # End flow
    b.link_nodes("a_kembali", "final")
    
    b.save(os.path.join(dest_dir, "admin_kelola_produk.drawio"))

    # 3. Admin Kelola Kategori
    b = DrawioDiagramBuilder("Activity Kelola Kategori", "act-adm-kelola-kat", 950, 850)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 380, 760)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 500, 40, 380, 760)
    b.add_node("start", "", b.ST_START, 235, 80, 30, 30)
    b.add_node("a1", "Membuka Halaman Kelola Kategori", b.ST_ACTION, 150, 140, 200, 55)
    b.add_node("s1", "Mengambil Data & Menampilkan Daftar\nKategori dan Form Tambah", b.ST_ACTION, 590, 140, 200, 55)
    b.add_node("dec_aksi", "Aksi Kategori?", b.ST_RHOMBUS, 190, 230, 120, 75)
    
    # Tambah Kategori
    b.add_node("a_tambah", "Menginput Nama Kategori & Simpan", b.ST_ACTION, 110, 340, 180, 55)
    b.add_node("s_tambah", "Memeriksa Keunikan Kategori & Simpan", b.ST_ACTION, 550, 340, 180, 55)
    
    # Hapus Kategori
    b.add_node("a_hapus", "Konfirmasi Hapus Kategori", b.ST_ACTION, 110, 430, 180, 55)
    b.add_node("s_hapus", "Hapus Kategori dari DB", b.ST_ACTION, 550, 430, 180, 55)
    
    # Kembali
    b.add_node("a_kembali", "Kembali ke Dashboard", b.ST_ACTION, 110, 520, 180, 55)
    
    b.add_node("s_refresh", "Me-refresh Halaman Kategori", b.ST_ACTION, 590, 610, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 235, 622, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 242, 629, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec_aksi", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(690, 210), (250, 210)])
    
    b.link_nodes("dec_aksi", "a_tambah", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(150, 267)])
    b.link_nodes("dec_aksi", "a_hapus", label="Hapus", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(150, 267)])
    b.link_nodes("dec_aksi", "a_kembali", label="Kembali", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    
    b.link_nodes("a_tambah", "s_tambah")
    b.link_nodes("a_hapus", "s_hapus")
    
    b.link_nodes("s_tambah", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(640, 410), (690, 410)])
    b.link_nodes("s_hapus", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(640, 500), (690, 500)])
    
    b.link_nodes("s_refresh", "s1", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(910, 637), (910, 167)])
    b.link_nodes("a_kembali", "final")
    
    b.save(os.path.join(dest_dir, "admin_kelola_kategori.drawio"))

    # 4. Admin Kelola Pesanan
    b = DrawioDiagramBuilder("Activity Kelola Pesanan", "act-adm-kelola-pes", 900, 750)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Membuka Halaman Kelola Pesanan", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Menampilkan Rincian Pesanan Masuk", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("a2", "Memeriksa Bukti Pembayaran Transaksi", b.ST_ACTION, 140, 230, 200, 55)
    b.add_node("dec", "Pembayaran\nValid?", b.ST_RHOMBUS, 200, 310, 120, 75)
    b.add_node("s2_ok", "Mengubah Status ke 'Processed'/'Shipped'\ndan Update Stok Produk", b.ST_ACTION, 560, 320, 200, 55)
    b.add_node("s2_fail", "Mengubah Status ke 'Pending' / Menolak\ndan Memberi Alasan Tolak", b.ST_ACTION, 560, 420, 200, 55)
    b.add_node("s3_noti", "Mengirim Notifikasi Status ke Dashboard User", b.ST_ACTION, 560, 510, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 522, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 529, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "a2")
    b.link_nodes("a2", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_fail", label="Tidak", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(260, 447)])
    b.link_nodes("s2_ok", "s3_noti")
    b.link_nodes("s2_fail", "s3_noti", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(660, 490), (660, 500)])
    b.link_nodes("s3_noti", "final")
    b.save(os.path.join(dest_dir, "admin_kelola_pesanan.drawio"))

    # 5. Admin Kelola Artikel Blog
    b = DrawioDiagramBuilder("Activity Kelola Artikel", "act-adm-kelola-art", 950, 850)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 380, 760)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 500, 40, 380, 760)
    b.add_node("start", "", b.ST_START, 235, 80, 30, 30)
    b.add_node("a1", "Membuka Halaman Kelola Artikel", b.ST_ACTION, 150, 140, 200, 55)
    b.add_node("s1", "Mengambil Data & Menampilkan Daftar Artikel", b.ST_ACTION, 590, 140, 200, 55)
    b.add_node("dec_aksi", "Aksi Artikel?", b.ST_RHOMBUS, 190, 230, 120, 75)
    
    # Tambah Artikel
    b.add_node("a_tambah", "Mengisi Form & Kirim Artikel Baru", b.ST_ACTION, 110, 340, 180, 55)
    b.add_node("s_tambah", "Validasi, Upload Gambar &\nSimpan Artikel Baru ke DB", b.ST_ACTION, 550, 340, 180, 55)
    
    # Edit Artikel
    b.add_node("a_edit", "Mengubah Isi Form & Kirim Update", b.ST_ACTION, 110, 430, 180, 55)
    b.add_node("s_edit", "Validasi & Update Artikel di DB", b.ST_ACTION, 550, 430, 180, 55)
    
    # Hapus Artikel
    b.add_node("a_hapus", "Konfirmasi Hapus Artikel", b.ST_ACTION, 110, 520, 180, 55)
    b.add_node("s_hapus", "Hapus Data Artikel dari DB", b.ST_ACTION, 550, 520, 180, 55)
    
    # Kembali
    b.add_node("a_kembali", "Kembali ke Dashboard", b.ST_ACTION, 110, 610, 180, 55)
    
    b.add_node("s_refresh", "Me-refresh Daftar Artikel", b.ST_ACTION, 590, 700, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 235, 712, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 242, 719, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec_aksi", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(690, 210), (250, 210)])
    
    b.link_nodes("dec_aksi", "a_tambah", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(150, 267)])
    b.link_nodes("dec_aksi", "a_edit", label="Edit", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(150, 267)])
    b.link_nodes("dec_aksi", "a_hapus", label="Hapus", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(150, 267)])
    b.link_nodes("dec_aksi", "a_kembali", label="Kembali", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    
    b.link_nodes("a_tambah", "s_tambah")
    b.link_nodes("a_edit", "s_edit")
    b.link_nodes("a_hapus", "s_hapus")
    
    b.link_nodes("s_tambah", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(640, 410), (690, 410)])
    b.link_nodes("s_edit", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(640, 500), (690, 500)])
    b.link_nodes("s_hapus", "s_refresh", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(640, 590), (690, 590)])
    
    b.link_nodes("s_refresh", "s1", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(910, 727), (910, 167)])
    b.link_nodes("a_kembali", "final")
    
    b.save(os.path.join(dest_dir, "admin_kelola_artikel.drawio"))


    # ================== PENGGUNA DIAGRAMS (5 File) ==================

    # 6. Registrasi Akun Pelanggan
    b = DrawioDiagramBuilder("Activity Registrasi Pelanggan", "act-usr-reg", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Membuka Form Registrasi\ndan Mengisi Data Diri Lengkap", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memvalidasi Format Email, Sandi,\ndan Ketersediaan Email di DB", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Valid & Unik?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Error Validasi\n(Email sudah terdaftar/Data kurang)", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Menyimpan Data Pengguna Baru\ndan Melakukan Hash Password", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("s3", "Mengarahkan Pelanggan ke Halaman Login", b.ST_ACTION, 560, 520, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 532, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 539, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "a1", points=[(780, 367), (780, 110), (240, 110)])
    b.link_nodes("s2_ok", "s3")
    b.link_nodes("s3", "final")
    b.save(os.path.join(dest_dir, "user_registrasi.drawio"))

    # 7. Login Pelanggan
    b = DrawioDiagramBuilder("Activity Login Pelanggan", "act-usr-login", 900, 650)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 560)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 560)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Mengisi Email & Password pada Form Login", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memvalidasi Kredensial Pelanggan", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Kredensial\nValid?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Error Kredensial Salah", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Mengatur Session Pengguna\ndan Redirect ke Halaman Beranda/Katalog", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 442, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 449, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "a1", points=[(780, 367), (780, 110), (240, 110)])
    b.link_nodes("s2_ok", "final")
    b.save(os.path.join(dest_dir, "user_login.drawio"))

    # 8. Pencarian & Filter Katalog Produk
    b = DrawioDiagramBuilder("Activity Cari & Filter Katalog", "act-usr-katalog", 900, 700)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 610)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 610)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Mengakses Halaman Katalog & Mengetik\nKeyword Pencarian / Memilih Filter Kategori", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memproses Query Pencarian di DB\n(Mencari Kategori/Nama Cocok)", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Produk Ditemukan?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Keterangan\n'Produk Tidak Ditemukan'", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Menampilkan Grid Produk Sesuai Hasil Filter", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 442, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 449, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "final", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(660, 410), (240, 410)])
    b.link_nodes("s2_ok", "final")
    b.save(os.path.join(dest_dir, "user_katalog_search.drawio"))

    # 9. Melakukan Checkout Pemesanan (Pemesanan Madu)
    b = DrawioDiagramBuilder("Activity Checkout Pesanan", "act-usr-checkout", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Klik Tombol Checkout pada Keranjang", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memeriksa Apakah User Sudah Login", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec_login", "Sudah Login?", b.ST_RHOMBUS, 600, 220, 120, 75)
    b.add_node("s2_login", "Mengarahkan User ke Halaman Login", b.ST_ACTION, 560, 320, 200, 55)
    b.add_node("a2_form", "Mengisi Form Checkout (Alamat,\nKurir Pengiriman, WhatsApp)", b.ST_ACTION, 140, 420, 200, 55)
    b.add_node("s3_order", "Menyimpan Data Pesanan, Total Harga,\ndan Membersihkan Item Keranjang", b.ST_ACTION, 560, 420, 200, 55)
    b.add_node("s4_invoice", "Menampilkan Halaman Pembayaran dengan\nNominal Unik Transfer Bank", b.ST_ACTION, 560, 520, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 532, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 539, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec_login")
    b.link_nodes("dec_login", "a2_form", label="Ya", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(250, 257)])
    b.link_nodes("dec_login", "s2_login", label="Tidak")
    b.link_nodes("s2_login", "a2_form", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(660, 400), (250, 400)])
    b.link_nodes("a2_form", "s3_order")
    b.link_nodes("s3_order", "s4_invoice")
    b.link_nodes("s4_invoice", "final")
    b.save(os.path.join(dest_dir, "user_checkout.drawio"))

    # 10. Upload Bukti Pembayaran
    b = DrawioDiagramBuilder("Activity Upload Bukti Pembayaran", "act-usr-upload", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Klik Kirim Bukti Pembayaran pada Riwayat", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Menampilkan Halaman Form Upload Bukti", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("a2", "Memilih File Struk dan Klik Unggah", b.ST_ACTION, 140, 230, 200, 55)
    b.add_node("s2", "Memvalidasi Ekstensi dan Ukuran File", b.ST_ACTION, 560, 230, 200, 55)
    b.add_node("dec", "File Valid?", b.ST_RHOMBUS, 600, 310, 120, 75)
    b.add_node("s3_err", "Menampilkan Pesan Error Validasi File", b.ST_ACTION, 560, 410, 200, 55)
    b.add_node("s3_ok", "Menyimpan Gambar ke Server & Update\nStatus Pesanan ke 'Paid / Pending Review'", b.ST_ACTION, 560, 490, 200, 55)
    b.add_node("s4", "Menampilkan Notifikasi Sukses Upload", b.ST_ACTION, 560, 570, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 582, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 589, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "a2")
    b.link_nodes("a2", "s2")
    b.link_nodes("s2", "dec")
    b.link_nodes("dec", "s3_ok", label="Ya")
    b.link_nodes("dec", "s3_err", label="Tidak")
    b.link_nodes("s3_err", "a2", points=[(780, 437), (780, 210), (240, 210)])
    b.link_nodes("s3_ok", "s4")
    b.link_nodes("s4", "final")
    b.save(os.path.join(dest_dir, "user_upload_bukti.drawio"))

    print("Successfully generated exactly 10 activity diagrams!")


if __name__ == "__main__":
    import sys
    dest = "/var/home/indra12/skripsi/MekarJaya/diagram/activity"
    if len(sys.argv) > 1:
        dest = sys.argv[1]
    generate_activities(dest)
