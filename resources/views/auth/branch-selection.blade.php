<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกสาขา - GUYSIRI CLINIC Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .selection-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            text-align: center;
        }
        .user-info .name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        .user-info .role {
            color: #667eea;
            font-size: 14px;
            margin-top: 5px;
        }
        .instruction {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .branch-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .branch-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .branch-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .branch-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }
        .branch-card input[type="radio"] {
            display: none;
        }
        .branch-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .branch-address {
            font-size: 13px;
            color: #666;
        }
        .branch-status {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            background: #10b981;
            color: white;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        .logout-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-link a:hover {
            text-decoration: underline;
        }
        .no-branches {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="selection-container">
        <div class="logo">
            <h1>GUYSIRI CLINIC</h1>
            <p class="subtitle">Management System</p>
        </div>

        <div class="user-info">
            <div class="name">{{ Auth::user()->name }}</div>
            <div class="role">{{ Auth::user()->role->name ?? 'ผู้ใช้งาน' }}</div>
        </div>

        <p class="instruction">กรุณาเลือกสาขาที่ต้องการเข้าใช้งาน</p>

        @if($branches->count() > 0)
        <form method="POST" action="{{ route('branch.switch') }}" id="branchForm">
            @csrf

            <div class="branch-grid">
                @foreach($branches as $branch)
                <label class="branch-card" onclick="selectBranch(this)">
                    <input type="radio" name="branch_id" value="{{ $branch->id }}" required>
                    <div class="branch-name">{{ $branch->name }}</div>
                    <div class="branch-address">{{ $branch->address ?? 'ไม่มีที่อยู่' }}</div>
                    <span class="branch-status">เปิดให้บริการ</span>
                </label>
                @endforeach
            </div>

            <button type="submit" class="btn" id="submitBtn" disabled>เข้าสู่ระบบ</button>
        </form>
        @else
        <div class="no-branches">
            <p>ไม่พบสาขาที่คุณสามารถเข้าถึงได้</p>
            <p style="margin-top: 10px; font-size: 13px;">กรุณาติดต่อผู้ดูแลระบบ</p>
        </div>
        @endif

        <div class="logout-link">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">ออกจากระบบ</a>
            </form>
        </div>
    </div>

    <script>
        function selectBranch(card) {
            // Remove selected class from all cards
            document.querySelectorAll('.branch-card').forEach(c => {
                c.classList.remove('selected');
            });

            // Add selected class to clicked card
            card.classList.add('selected');

            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
        }
    </script>
</body>
</html>
