const { createApp, reactive, ref, computed, onMounted, nextTick } = Vue;

createApp({
  setup() {
    // 響應式數據
    const currentPage = ref('home');
    const authMode = ref('login');
    const showEditModal = ref(false);
    const isLoggedIn = ref(false);
    const currentUser = ref(null);
    const loading = ref(false);
    
    // 表單數據
    const authForm = reactive({
      username: '',
      password: '',
      confirmPassword: '',
      email: ''
    });
    
    const passwordForm = reactive({
      oldPassword: '',
      newPassword: '',
      confirmPassword: ''
    });
    
    // 計畫數據
    const currentPlan = reactive({
      id: '',
      name: '新的旅遊計畫',
      created: '',
      items: []
    });
    
    const editingItem = ref(null);
    const userPlans = ref([]);
    const users = ref([]);
    
    // 通知系統
    const notification = reactive({
      show: false,
      message: '',
      type: 'info'
    });
    
    // 計算屬性
    const recentPlans = computed(() => {
      if (!isLoggedIn.value) return [];
      return userPlans.value.slice(0, 3);
    });
    
    const sortedPlanItems = computed(() => {
      return [...currentPlan.items].sort((a, b) => {
        const dateTimeA = new Date(`${a.date} ${a.time}`);
        const dateTimeB = new Date(`${b.date} ${b.time}`);
        return dateTimeA - dateTimeB;
      });
    });
    
    // 工具函數
    const generateId = () => {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };
    
    const showNotification = (message, type = 'info') => {
      notification.message = message;
      notification.type = type;
      notification.show = true;
      setTimeout(() => {
        notification.show = false;
      }, 3000);
    };
    
    // 本地儲存功能 - 使用假的localStorage替代
    const storage = ref({});
    
    const saveToStorage = (key, data) => {
      try {
        storage.value[key] = JSON.stringify(data);
        console.log(`儲存 ${key}:`, data);
      } catch (error) {
        console.error('儲存失敗:', error);
        showNotification('儲存失敗', 'error');
      }
    };
    
    const loadFromStorage = (key) => {
      try {
        const data = storage.value[key];
        return data ? JSON.parse(data) : null;
      } catch (error) {
        console.error('讀取失敗:', error);
        return null;
      }
    };

    //註冊
    const handleRegister = async () => {
      if (authForm.password !== authForm.confirmPassword) {
        showNotification('密碼不一致', 'error');
        return;
      }

      try {
        const response = await fetch('http://localhost:8010/api/users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: authForm.username,
            email: authForm.email,
            password: authForm.password
          })
        });

        const data = await response.json();

        if (response.ok) {
          showNotification('註冊成功', 'success');
          authMode.value = 'login';
          Object.keys(authForm).forEach(key => authForm[key] = '');
        } else {
          showNotification('註冊失敗: ' + (data.message || ''), 'error');
        }
      } catch (error) {
        showNotification('網路錯誤', 'error');
      }
    };
    //登入驗證
    const handleLogin = async () => {
      loading.value = true;
      const start = Date.now();
      try {
        const response = await fetch('http://localhost:8010/api/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: authForm.username,
            password: authForm.password
          })
        });

        const data = await response.json();

        if (response.ok) {
          // 儲存 token
          localStorage.setItem('token', data.token);
          
          currentUser.value = data.user;
          currentUser.value = {
            ...data.user,
            token: data.token,
            plans: []
          };

          isLoggedIn.value = true;

          showNotification('登入成功', 'success');
          currentPage.value = 'home';

          Object.keys(authForm).forEach(key => authForm[key] = '');
        } else {
          showNotification('帳號或密碼錯誤', 'error');
        }
      } catch (error) {
        showNotification('網路錯誤', 'error');
      } finally {
        const elapsed = Date.now() - start;
        const remain = 3500 - elapsed;
        setTimeout(() => {
          loading.value = false;
        }, remain > 0 ? remain : 0);
      }
    };
  
    const handleAuth = async () => {
      if (authMode.value === 'register') {
        await handleRegister();
      } else {
        await handleLogin();
      }
    };

    
    const logout = () => {
      currentUser.value = null;
      isLoggedIn.value = false;
      userPlans.value = [];
      delete storage.value.currentUser;
      showNotification('已登出', 'info');
      currentPage.value = 'home';
    };
    
    const changePassword = () => {
      if (!currentUser.value) return;
      
      if (passwordForm.oldPassword !== currentUser.value.password) {
        showNotification('舊密碼錯誤', 'error');
        return;
      }
      
      if (passwordForm.newPassword !== passwordForm.confirmPassword) {
        showNotification('新密碼不一致', 'error');
        return;
      }
      
      // 更新密碼
      currentUser.value.password = passwordForm.newPassword;
      const userIndex = users.value.findIndex(u => u.id === currentUser.value.id);
      if (userIndex !== -1) {
        users.value[userIndex] = currentUser.value;
        saveToStorage('users', users.value);
        saveToStorage('currentUser', currentUser.value);
      }
      
      showNotification('密碼已更新', 'success');
      
      // 重置表單
      Object.keys(passwordForm).forEach(key => {
        passwordForm[key] = '';
      });
    };
    
    // 計畫管理功能
    const loadUserPlans = () => {
      if (!currentUser.value) return;
      
      const allPlans = loadFromStorage('plans') || [];
      userPlans.value = allPlans.filter(plan => 
        currentUser.value.plans.includes(plan.id)
      );
    };
    
    const savePlan = async() => {
      if (!currentPlan.name.trim()) {
        showNotification('請輸入計畫名稱', 'error');
        return;
      }
      
      if (!currentPlan.id) {
        currentPlan.id = generateId();
        currentPlan.created = new Date().toISOString().split('T')[0];
      }
      
      // 儲存到全域計畫列表
      const allPlans = loadFromStorage('plans') || [];
      const existingIndex = allPlans.findIndex(p => p.id === currentPlan.id);
      
      const planToSave = {
        id: currentPlan.id,
        name: currentPlan.name,
        created: currentPlan.created,
        items: currentPlan.items.map(item => ({...item}))
      };
      
      if (existingIndex !== -1) {
        allPlans[existingIndex] = planToSave;
      } else {
        allPlans.push(planToSave);
      }
      
      saveToStorage('plans', allPlans);
      // 如果使用者已登入，更新使用者的計畫列表
      if (isLoggedIn.value && currentUser.value) {

        const planName = document.querySelector('.plan-name-input').value;
        try {
          // 新增計畫
          const planResponse = await fetch('http://localhost:8010/api/plan', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${currentUser.value.token}`
            },
            body: JSON.stringify({
              plan_title: planName
            })
          });
          if (!planResponse.ok) throw new Error('新增計畫失敗');
          const planData = await planResponse.json();
          const planId = planData.plan_id; // 後端回傳的 plan_id

          //新增所有行程
          for (const item of currentPlan.items) {
            await fetch('http://localhost:8010/api/journeys', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${currentUser.value.token}`
              },
              body: JSON.stringify({
                plan_id: planId,
                date: item.date,
                time: item.time,
                journey_title: item.activity,
                links: Array.isArray(item.links) ? item.links.filter(l => l) : [],
                image: item.images ? item.images[0] : null,
                notes: item.notes || null
              })
            });
          }

          // 更新本地 plans 關聯
          if (!currentUser.value.plans.includes(planId)) {
            currentUser.value.plans.push(planId);
            const userIndex = users.value.findIndex(u => u.id === currentUser.value.id);
            if (userIndex !== -1) {
              users.value[userIndex] = currentUser.value;
              saveToStorage('users', users.value);
              saveToStorage('currentUser', currentUser.value);
            }
          }
          loadUserPlans();
          showNotification('計畫與行程已同步到雲端', 'success');
        } catch (err) {
          showNotification('雲端儲存失敗：' + err.message, 'error');
        }
      } else {
        showNotification('計畫已儲存', 'success');
      }
    };
    
    const loadPlan = (planId) => {
      const allPlans = loadFromStorage('plans') || [];
      const plan = allPlans.find(p => p.id === planId);
      
      if (plan) {
        currentPlan.id = plan.id;
        currentPlan.name = plan.name;
        currentPlan.created = plan.created;
        currentPlan.items = plan.items.map(item => ({...item}));
        currentPage.value = 'planner';
      }
    };
    
    const deletePlan = (planId) => {
      if (!confirm('確定要刪除這個計畫嗎？')) return;
      
      // 從全域計畫列表刪除
      const allPlans = loadFromStorage('plans') || [];
      const filteredPlans = allPlans.filter(p => p.id !== planId);
      saveToStorage('plans', filteredPlans);
      
      // 從使用者計畫列表刪除
      if (currentUser.value) {
        currentUser.value.plans = currentUser.value.plans.filter(id => id !== planId);
        const userIndex = users.value.findIndex(u => u.id === currentUser.value.id);
        if (userIndex !== -1) {
          users.value[userIndex] = currentUser.value;
          saveToStorage('users', users.value);
          saveToStorage('currentUser', currentUser.value);
        }
        loadUserPlans();
      }
      
      showNotification('計畫已刪除', 'success');
    };
    
    // 行程項目管理
    const addNewItem = () => {
      const today = new Date();
      const newItem = {
        id: generateId(),
        date: today.toISOString().split('T')[0],
        time: '09:00',
        activity: '',
        links: [''],
        images: [],
        notes: ''
      };
      
      currentPlan.items.push(newItem);
      showNotification('新增行程項目', 'success');
    };
    
    const removeItem = (index) => {
      if (confirm('確定要刪除這個行程嗎？')) {
        currentPlan.items.splice(index, 1);
        showNotification('行程已刪除', 'info');
      }
    };
    
    const editItem = (item) => {
      editingItem.value = JSON.parse(JSON.stringify(item));
      showEditModal.value = true;
    };
    
    const saveEditedItem = () => {
      if (!editingItem.value) return;
      
      const index = currentPlan.items.findIndex(item => item.id === editingItem.value.id);
      if (index !== -1) {
        currentPlan.items[index] = JSON.parse(JSON.stringify(editingItem.value));
      }
      
      closeEditModal();
      showNotification('行程已更新', 'success');
    };
    
    const closeEditModal = () => {
      showEditModal.value = false;
      editingItem.value = null;
    };
    
    // 連結管理
    const addLink = (item) => {
      if (!item.links) {
        item.links = [];
      }
      item.links.push('');
    };
    
    const removeLink = (item, index) => {
      if (item.links && item.links.length > 1) {
        item.links.splice(index, 1);
      }
    };
    
    // 圖片上傳
    const handleImageUpload = (event, item) => {
      const files = event.target.files;
      if (!files || files.length === 0) return;
      
      Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            if (!item.images) {
              item.images = [];
            }
            item.images.push(e.target.result);
          };
          reader.readAsDataURL(file);
        }
      });
      
      // 重置文件輸入
      event.target.value = '';
      showNotification('圖片已上傳', 'success');
    };
    
    // PDF 匯出功能
    const exportToPDF = async () => {
      if (!currentPlan.name.trim()) {
        showNotification('請先輸入計畫名稱', 'error');
        return;
      }
      
      if (currentPlan.items.length === 0) {
        showNotification('計畫中沒有行程項目', 'error');
        return;
      }
      
      showNotification('正在生成PDF...', 'info');
      
      try {
        // 切換到預覽頁面進行PDF匯出
        const originalPage = currentPage.value;
        currentPage.value = 'preview';
        
        // 等待DOM更新
        await nextTick();
        
        setTimeout(() => {
          const element = document.getElementById('pdf-content');
          if (!element) {
            showNotification('找不到PDF內容元素', 'error');
            currentPage.value = originalPage;
            return;
          }
          
          if (typeof html2pdf === 'undefined') {
            showNotification('PDF功能暫時無法使用，請嘗試手動列印頁面', 'warning');
            window.print();
            return;
          }
          
          const opt = {
            margin: 1,
            filename: `${currentPlan.name}.pdf`,
            image: { type: 'jpeg', quality: 0.8 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
          };
          
          html2pdf().set(opt).from(element).save().then(() => {
            showNotification('PDF匯出成功', 'success');
          }).catch((error) => {
            console.error('PDF匯出錯誤:', error);
            showNotification('PDF匯出失敗，嘗試列印頁面', 'warning');
            window.print();
          }).finally(() => {
            currentPage.value = originalPage;
          });
        }, 500);
        
      } catch (error) {
        console.error('PDF匯出過程錯誤:', error);
        showNotification('PDF匯出失敗', 'error');
      }
    };
    
    // 初始化函數
    const initializeApp = () => {
      // 載入使用者資料
      const savedUsers = loadFromStorage('users');
      if (savedUsers) {
        users.value = savedUsers;
      }
      
      // 檢查登入狀態
      const savedUser = loadFromStorage('currentUser');
      if (savedUser) {
        const user = users.value.find(u => u.id === savedUser.id);
        if (user) {
          currentUser.value = user;
          isLoggedIn.value = true;
          loadUserPlans();
        }
      }
      
      // 載入範例計畫（首次使用）
      const existingPlans = loadFromStorage('plans');
      if (!existingPlans || existingPlans.length === 0) {
        const samplePlan = {
          id: "plan001",
          name: "京都三日遊",
          created: "2023-06-01",
          items: [
            {
              id: "item001",
              date: "2023-07-15",
              time: "09:00",
              activity: "參觀清水寺",
              links: ["https://www.kiyomizudera.or.jp/"],
              images: [],
              notes: "記得早點去避開人潮"
            },
            {
              id: "item002", 
              date: "2023-07-15",
              time: "14:00",
              activity: "漫步祇園區",
              links: ["https://www.japan-guide.com/e/e3901.html"],
              images: [],
              notes: "可能遇到藝妓"
            },
            {
              id: "item003",
              date: "2023-07-16", 
              time: "10:00",
              activity: "金閣寺",
              links: ["https://www.shokoku-ji.jp/k_about.html"],
              images: [],
              notes: "最佳拍照時間"
            }
          ]
        };
        saveToStorage('plans', [samplePlan]);
      }
    };
    
    // 新建計畫
    const createNewPlan = () => {
      currentPlan.id = '';
      currentPlan.name = '新的旅遊計畫';
      currentPlan.created = '';
      currentPlan.items = [];
      currentPage.value = 'planner';
    };
    
    // 處理輸入更新
    const updateItemField = (item, field, value) => {
      item[field] = value;
    };
    
    // 生命週期
    onMounted(() => {
      initializeApp();
    });
    
    // 返回所有需要在模板中使用的數據和方法
    return {
      // 響應式數據
      currentPage,
      authMode,  
      showEditModal,
      isLoggedIn,
      currentUser,
      authForm,
      passwordForm,
      currentPlan,
      editingItem,
      userPlans,
      notification,
      loading,
      
      // 計算屬性
      recentPlans,
      sortedPlanItems,
      
      // 方法
      formatDate,
      showNotification,
      handleAuth,
      logout,
      changePassword,
      savePlan,
      loadPlan,
      deletePlan,
      addNewItem,
      removeItem,
      editItem,
      saveEditedItem,
      closeEditModal,
      addLink,
      removeLink,
      handleImageUpload,
      exportToPDF,
      createNewPlan,
      updateItemField
    };
  }
}).mount('#app');